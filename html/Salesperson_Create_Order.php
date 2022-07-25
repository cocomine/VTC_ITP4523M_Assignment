<?php
$conn = null;
$title = "Create Order";
require_once "function/head.inc.php";

//is post request
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    ob_clean();
    header("Content-Type: text/json");

    /* is create order */
    if (isset($_GET['create-order'])) {

        $data = json_decode(file_get_contents("php://input"));
        $data->cusEmail = filter_var($data->cusEmail, FILTER_SANITIZE_STRING);
        $data->cusName = filter_var($data->cusName, FILTER_SANITIZE_STRING);
        $data->cusPhone = filter_var($data->cusPhone, FILTER_SANITIZE_STRING);
        //set null to not need delivery
        $dAddress = $data->dAddress ?? NULL;
        $dDate = $data->dDate ?? NULL;
        $dAddress = filter_var($dAddress, FILTER_SANITIZE_STRING);
        $dDate = filter_var($dDate, FILTER_SANITIZE_STRING);

        //generate id
        $orderID = rand(0, 99999);

        try {
            //count total price
            $total = 0;
            foreach ($data->items as $key => $val) {
                $stmt = $conn->prepare("SELECT price FROM item WHERE itemID = ?");
                $stmt->bind_param("s", $key);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $total += $row['price'] * $val;
                } else {
                    http_response_code(400);
                    exit();
                }
            }

            //count discount
            $curl = curl_init("http://127.0.0.1:8080/api/discountCalculator?discount=".$total);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $total = curl_exec($curl);
            curl_close($curl);
            if(!$total){
                echo json_encode(array("result" => "Fail"));
                exit();
            }

            //add customer
            try {
                $stmt = $conn->prepare("INSERT INTO customer VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $data->cusEmail, $data->cusName, $data->cusPhone);
                $stmt->execute();
            } catch (mysqli_sql_exception $e) {
                $stmt = $conn->prepare("UPDATE customer SET customerName = ?, phoneNumber = ? WHERE customerEmail = ?");
                $stmt->bind_param("sss", $data->cusName, $data->cusPhone, $data->cusEmail);
                $stmt->execute();
            }

            //create order
            $stmt = $conn->prepare("INSERT INTO orders VALUES (?, ?, ?, CURRENT_TIMESTAMP(), ?, ?, ?)");
            $stmt->bind_param("sssssi", $orderID, $data->cusEmail, $_SESSION['staffID'], $dAddress, $dDate, $total);
            if (!$stmt->execute()) {
                //fail
                http_response_code(400);
                exit();
            }

            //add item
            foreach ($data->items as $key => $val) {
                $stmt = $conn->prepare("INSERT INTO itemorders VALUES (?, ?, ?, (SELECT price*? FROM item WHERE itemID = ?))");
                $stmt->bind_param("ssiis", $orderID, $key, $val, $val, $key);
                if (!$stmt->execute()) {
                    http_response_code(400);
                    exit();
                }
            }

            //success
            echo json_encode(array("result" => "Success", "data" => array("orderID" => $orderID, "Total" => $total)));
        } catch (mysqli_sql_exception $e) {
            echo json_encode(array("result" => "Fail"));
        }
    } /* is get all item */
    elseif (isset($_GET['get-item'])) {
        $stmt = $conn->prepare("SELECT * FROM item WHERE stockQuantity > 0");
        $stmt->execute();
        $result = $stmt->get_result();

        //get data
        $output = array();
        while ($row = $result->fetch_assoc()) {
            $output[] = array(
                "ItemID" => $row['itemID'],
                "Name" => $row['itemName'],
                "Description" => $row['itemDescription'],
                "Stock" => $row['stockQuantity'],
                "Price" => $row['price']
            );
        }

        echo json_encode($output); //output
    } /* nothing match */
    else {
        http_response_code(404);
    }

    ob_end_flush();
    exit();
}
?>
    <!--Menu-->
    <div class="sidebar-menu">
        <div class="sidebar-header">
            <div class="logo">
                <a href="/">Sales System</a>
            </div>
        </div>
        <div class="main-menu">
            <div class="menu-inner">
                <nav>
                    <ul class="metismenu" id="menu">
                        <li class="active">
                            <a href="javascript:void(0)" aria-expanded="true"></i><span>Order</span></a>
                            <ul class="collapse">
                                <li><a href="/">View Order</a></li>
                                <li class="active"><a href="Salesperson_Create_Order.php">Create Order</a></li>
                            </ul>
                        </li>

                        <?php
                        //is Manager will show
                        if ($_SESSION['position'] == 1) {
                            echo '
                            <li>
                                <a href="javascript:void(0)" aria-expanded="false"><span>Item</span></a>
                                <ul class="collapse">
                                    <li><a href="view-item.php">View Item</a></li>
                                    <li><a href="Manger_Insert_and_Edit_Item.php">Insert and Edit Item</a></li>
                                </ul>
                            </li>
                            <li><a href="Manager_View_Customer.php"><span>View Customer</span></a></li>
                            <li><a href="Manager_Generate_Report.php"><span>Generate Report</span></a></li>';
                        }
                        ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!--Header-->
    <div class="main-content">
        <div class="header-area">
            <div class="row align-items-center">
                <!--Nav Button-->
                <div class="col-md-6 col-sm-8 clearfix">
                    <div class="nav-btn pull-left">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <!--Directory-->
                    <h4 class="page-title pull-left">Create Order</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a href="index.php">Order</a></li>
                        <li><span>Create Order</span></li>
                    </ul>
                </div>
                <!--User Profile-->
                <div class="col-md-6 col-sm-4 clearfix">
                    <ul class="notification-area pull-right">
                        <ul class="user-profile pull-right">
                            <h4 class="user-name dropdown-toggle" data-bs-toggle="dropdown" id="username" aria-expanded="false"><?php echo $_SESSION['staffName'] ?>
                                <i class="fa fa-angle-down"></i></h4>
                            <div class="dropdown-menu" aria-labelledby="username">
                                <a class="dropdown-item" href="logout.php" id="logout">Log Out</a>
                            </div>
                        </ul>
                    </ul>
                </div>
            </div>
        </div>

        <!--Main-->
        <div class="main-content-inner">
            <div class="row">
                <!--Create Form Start-->
                <!--Order ID和Order Date/Time 不用input,用funcition gen出來-->
                <div class="col-12 col-md-6">
                    <div class="row">
                        <div class="col-12 mt-5">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Create Order Form</h4>
                                    <form id="create-order" class="needs-validation" novalidate>
                                        <div class="form-group">
                                            <label for="cusName" class="col-form-label">Customer's Name
                                                <span style="color: red">*</span></label>
                                            <input type="text" class="form-control" id="cusName" required name="cusName" autocomplete="name">
                                            <div class="invalid-feedback">
                                                Please fill in customer name
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="cusEmail" class="col-form-label">Customer's Email
                                                <span style="color: red">*</span></label>
                                            <input type="email" class="form-control" id="cusEmail" required name="cusEmail">
                                            <div class="invalid-feedback">
                                                Please fill in customer email
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="cusPhone" class="col-form-label">Customer's Phone
                                                <span style="color: red">*</span></label>
                                            <input class="form-control" type="tel" id="cusPhone" required name="cusPhone">
                                            <div class="invalid-feedback">
                                                Please fill in customer phone number
                                            </div>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="needDelivery">
                                            <label class="form-check-label" for="needDelivery">
                                                Need Delivery
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label for="dAddress" class="col-form-label">Delivery Address <span style="color: red">*</span></label>
                                            <textarea class="form-control" type="text" id="dAddress" name="dAddress" required autocomplete="street-address" disabled></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="dDate" class="col-form-label">Delivery Date <span style="color: red">*</span></label>
                                            <input class="form-control" type="date" id="dDate" name="dDate" required disabled>
                                        </div>
                                        <div class="form-group">
                                            <p class="mt-4"><span style="color: red">*</span> Required</p>
                                            <button id="form_submit" type="submit" class="btn btn-rounded btn-primary pr-4 pl-4">
                                                Submit
                                            </button>
                                            <button id="form_submit" type="reset" class="btn btn-rounded btn-outline-secondary pr-4 pl-4 m-1">
                                                Reset
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--in cart item-->
                <div class="col-12 col-md-6">
                    <div class="row">
                        <div class="col-12 mt-5">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Shopping Cart</h4>
                                    <div class="single-table">
                                        <div class="table-responsive">
                                            <table class="table table-striped text-center" id="shoppingCart">
                                                <thead class="text-uppercase">
                                                <tr>
                                                    <th>Item ID</th>
                                                    <th>Item Name</th>
                                                    <th>Quantity</th>
                                                    <th>Remove</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Item List Start-->
                <div class="col-12 mt-5">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title">Item List</h4>
                            <div class="data-tables datatable-dark">
                                <table id="dataTable" class="text-center">
                                    <thead class="text-capitalize">
                                    <tr>
                                        <th>Item ID</th>
                                        <th>Item Name</th>
                                        <th>Stock Qty</th>
                                        <th>Price</th>
                                        <th>Item Description</th>
                                        <th>Add To Order</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- load this page script -->
    <script>
        const load_script = ["./assets/js/page/Create_order.js"];
    </script>

<?php
require_once "function/footer.inc.php";