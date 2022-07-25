<?php
$conn = null;
$title = "View Order";
require_once "function/head.inc.php";

//is post request
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    ob_clean();
    header("Content-Type: text/json");

    /* is get order delivery info */
    if (isset($_GET['order-info'])) {
        if (!empty($_GET['order-info'])) {
            $orderID = filter_var($_GET['order-info'], FILTER_SANITIZE_NUMBER_INT);

            $stmt = $conn->prepare("SELECT orderID, deliveryAddress, deliveryDate FROM orders WHERE orderID = ? LIMIT 1");
            $stmt->bind_param("s", $orderID);
            $stmt->execute();
            $result = $stmt->get_result();

            $row = $result->fetch_assoc();
            echo json_encode(array(
                "OrderID" => $row['orderID'],
                "Address" => $row['deliveryAddress'],
                "Date" => $row['deliveryDate'] == "0000-00-00" ? null : $row['deliveryDate']
            ));
        } else {
            http_response_code(400); //is empty
        }
    } /* is delete order */
    elseif (isset($_GET['delete-order'])) {
        if (!empty($_GET['delete-order'])) {
            $orderID = filter_var($_GET['delete-order'], FILTER_SANITIZE_NUMBER_INT);

            try {
                $stmt = $conn->prepare("DELETE FROM itemorders WHERE orderID = ?");
                $stmt->bind_param("s", $orderID);
                $stmt2 = $conn->prepare("DELETE FROM orders WHERE orderID = ?");
                $stmt2->bind_param("s", $orderID);

                $stmt->execute();
                $stmt2->execute();
                echo json_encode(array("result" => "Success")); //is deleted
            } catch (mysqli_sql_exception $e) {
                echo json_encode(array("result" => "Fail"));
            }
        } else {
            http_response_code(400); //is empty
        }
    } /* is get order item list */
    elseif (isset($_GET['order-items'])) {
        if (!empty($_GET['order-items'])) {
            $orderID = filter_var($_GET['order-items'], FILTER_SANITIZE_NUMBER_INT);

            $stmt = $conn->prepare("SELECT i.itemID, i.itemName, o.orderQuantity, o.price FROM itemorders o, item i WHERE o.itemID = i.itemID AND o.orderID = ? ORDER BY i.itemName DESC");
            $stmt->bind_param("s", $orderID);
            $stmt->execute();
            $result = $stmt->get_result();

            //get data
            $output = array();
            while ($row = $result->fetch_assoc()) {
                $output[] = array(
                    "ItemID" => $row['itemID'],
                    "Name" => $row['itemName'],
                    "Qty" => $row['orderQuantity'],
                    "TotalPrice" => $row['price']
                );
            }

            echo json_encode($output); //output
        } else {
            http_response_code(400); //is empty
        }

    } /* is update order delivery info */
    elseif (isset($_GET['update-order'])) {
        if (!empty($_GET['update-order'])) {
            $orderID = filter_var($_GET['update-order'], FILTER_SANITIZE_NUMBER_INT); //sterilize

            $data = json_decode(file_get_contents("php://input"));
            $data->dAddress = filter_var($data->dAddress, FILTER_SANITIZE_STRING);
            $data->dDate = filter_var($data->dDate, FILTER_SANITIZE_STRING);

            try {
                $stmt = $conn->prepare("UPDATE orders SET deliveryAddress = ?, deliveryDate = ? WHERE orderID = ?");
                $stmt->bind_param("sss", $data->dAddress, $data->dDate, $orderID);

                $stmt->execute();
                echo json_encode(array("result" => "Success")); //is updated
            } catch (mysqli_sql_exception $e) {
                echo json_encode(array("result" => "Fail"));
            }
        } else {
            http_response_code(400); //is empty
        }
    } /* is get all order */
    elseif (isset($_GET['get-order'])) {
        $stmt = $conn->prepare("SELECT o.orderID, c.customerEmail, c.customerName, c.phoneNumber, s.staffID, s.staffName, o.dateTime, o.deliveryAddress, o.deliveryDate, o.totalPrice FROM orders o, staff s, customer c WHERE s.staffID = o.staffID AND o.customerEmail = c.customerEmail;");
        $stmt->execute();
        $result = $stmt->get_result();

        //get data
        $output = array();
        while ($row = $result->fetch_assoc()) {
            $output[] = array(
                "OrderID" => $row['orderID'],
                "Email" => $row['customerEmail'],
                "Name" => $row['customerName'],
                "Phone" => $row['phoneNumber'],
                "StaffID" => $row['staffID'],
                "StaffName" => $row['staffName'],
                "DateTime" => $row['dateTime'],
                "Address" => $row['deliveryAddress'],
                "DeliveryDate" => $row['deliveryDate'] == "0000-00-00" ? null : $row['deliveryDate'],
                "Total" => $row['totalPrice'],
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
                                <li class="active"><a href="/">View Order</a></li>
                                <li><a href="Salesperson_Create_Order.php">Create Order</a></li>
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
                    <h4 class="page-title pull-left">View Order</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a href="index.html">Order</a></li>
                        <li><span>View Order</span></li>
                    </ul>
                </div>
                <!--User Profile-->
                <div class="col-md-6 col-sm-4 clearfix">
                    <ul class="notification-area pull-right">
                        <ul class="user-profile pull-right">
                            <h4 class="user-name dropdown-toggle" data-bs-toggle="dropdown" id="username"><?php echo $_SESSION['staffName'] ?>
                                <i class="fa fa-angle-down"></i></h4>
                            <div class="dropdown-menu">
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
                <!--Order List Start-->
                <div class="col-12 mt-5">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title">Order List</h4>
                            <div class="data-tables datatable-dark">
                                <table id="dataTable" class="text-center">
                                    <thead class="text-capitalize">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer's Name</th>
                                        <th>Customer's Email</th>
                                        <th>Customer's Phone</th>
                                        <th>Staff ID</th>
                                        <th>Staff's Name</th>
                                        <th>Order Date & Time</th>
                                        <th>Delivery Address</th>
                                        <th>Delivery Date</th>
                                        <th>Total Price</th>
                                        <th>Action</th>
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

    <!-- show order item -->
    <div class="modal fade" id="order-item" tabindex="-1" aria-labelledby="Order Item" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="single-table">
                        <div class="table-responsive">
                            <table class="table table-striped text-center" id="orderItems">
                                <thead class="text-uppercase">
                                <tr>
                                    <th>Item ID</th>
                                    <th>Item Name</th>
                                    <th>Quantity</th>
                                    <th>Total Price</th>
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

    <!-- Edit delivery informationEdit delivery information -->
    <div class="modal fade" id="edit-order" tabindex="-1" aria-labelledby="Order Item" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit delivery information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editOrder" class="needs-validation" novalidate data-ss-orderid="000000">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="dAddress" class="col-form-label">Delivery Address
                                <span style="color: red">*</span></label>
                            <textarea class="form-control" type="text" id="dAddress" name="dAddress" required></textarea>
                            <div class="invalid-feedback">
                                Please fill in delivery address
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="dDate" class="col-form-label">Delivery Date
                                <span style="color: red">*</span></label>
                            <input class="form-control" type="date" id="dDate" name="dDate" required>
                            <div class="invalid-feedback">
                                Please fill in delivery date
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <p class="mt-4"><span style="color: red">*</span> Required</p>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- load this page script -->
    <script>
        const load_script = ["./assets/js/page/view-order.js"];
    </script>
<?php
require_once "function/footer.inc.php";