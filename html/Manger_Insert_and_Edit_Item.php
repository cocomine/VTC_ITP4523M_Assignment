<?php
$conn = null;
$title = "Insert and Edit Item";
require_once "function/head.inc.php";

//check is manger
if($_SESSION['position'] != 1){
    if($_SERVER['REQUEST_METHOD'] == "POST") http_response_code(403);
    else header("location: ".$_SERVER['HTTP_REFERER']);
}

//is post request
if($_SERVER['REQUEST_METHOD'] == "POST"){
    ob_clean();
    header("Content-Type: text/json");

    if(isset($_GET['edit-item'])){
        //is edit item
        if (!empty($_GET['edit-item'])) {
            //SANITIZE
            $itemID = filter_var($_GET['edit-item'], FILTER_SANITIZE_NUMBER_INT);
            $data = json_decode(file_get_contents("php://input"));
            $data->iDescription = filter_var($data->iDescription, FILTER_SANITIZE_STRING);
            $data->itemName = filter_var($data->itemName, FILTER_SANITIZE_STRING);
            $data->itemPrice = filter_var($data->itemPrice, FILTER_SANITIZE_NUMBER_INT);
            $data->stockQty = filter_var($data->stockQty, FILTER_SANITIZE_NUMBER_INT);

            try {
                $stmt = $conn->prepare("UPDATE item SET itemName = ?, itemDescription = ?, stockQuantity = ?, price = ? WHERE itemID = ?");
                $stmt->bind_param("ssiis", $data->itemName, $data->iDescription, $data->stockQty, $data->itemPrice, $itemID);
                $stmt->execute();

                echo json_encode(array("result" => "Success")); //is update
            }catch (mysqli_sql_exception $e) {
                echo json_encode(array("result" => "Fail"));
            }
        } else {
            http_response_code(400); //is empty
        }
    }

    elseif (isset($_GET['add-item'])){
        //is add item

        //SANITIZE
        $data = json_decode(file_get_contents("php://input"));
        $data->iDescription = filter_var($data->iDescription, FILTER_SANITIZE_STRING);
        $data->itemName = filter_var($data->itemName, FILTER_SANITIZE_STRING);
        $data->itemPrice = filter_var($data->itemPrice, FILTER_SANITIZE_NUMBER_INT);
        $data->stockQty = filter_var($data->stockQty, FILTER_SANITIZE_NUMBER_INT);

        try {
            //generate id
            $stmt = $conn->prepare("SELECT MAX(itemID)+1 AS 'ID' FROM item");
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $itemID = $row['ID'];

            $stmt = $conn->prepare("INSERT INTO item VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issii", $itemID, $data->itemName, $data->iDescription, $data->stockQty, $data->itemPrice);
            $stmt->execute();

            echo json_encode(array("result" => "Success", "data" => array("ItemID" => $itemID))); //is update
        }catch (mysqli_sql_exception $e) {
            echo json_encode(array("result" => "Fail"));
        }
    }
    elseif (isset($_GET['get-item'])){
        //is get all item

        $stmt = $conn->prepare("SELECT * FROM item");
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
    }else{
        //nothing match
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
                        <li>
                            <a href="javascript:void(0)" aria-expanded="true"></i><span>Order</span></a>
                            <ul class="collapse">
                                <li><a href="/">View Order</a></li>
                                <li><a href="Salesperson_Create_Order.php">Create Order</a></li>
                            </ul>
                        </li>

                        <li class="active">
                            <a href="javascript:void(0)" aria-expanded="false">
                                <span>Item</span></a>
                            <ul class="collapse">
                                <li><a href="view-item.php">View Item</a></li>
                                <li class="active"><a href="Manger_Insert_and_Edit_Item.php">Insert and Edit Item</a>
                                </li>
                            </ul>
                        </li>

                        <li>
                            <a href="Manager_View_Customer.php"><span>View Customer</span></a>
                        </li>

                        <li>
                            <a href="Manager_Generate_Report.php"><span>Generate Report</span></a>
                        </li>
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
                    <h4 class="page-title pull-left">Insert and Edit Item</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a href="view-item.php">Item</a></li>
                        <li><span>Insert and Edit Item</span></li>
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
                                        <th>Stock Quantity</th>
                                        <th>Per Price</th>
                                        <th>Item Description</th>
                                        <th>Edit</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Insert Item Form Start-->
                <!--Item ID不用input,用funcition gen出來-->
                <div class="col-12">
                    <div class="row">
                        <div class="col-12 mt-5">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="header-title">Insert Item Form</h4>
                                    <form id="addItem" class="needs-validation" novalidate>
                                        <div class="form-group">
                                            <label for="add-itemName" class="col-form-label">Item Name
                                                <span style="color: red">*</span></label>
                                            <input class="form-control" type="text" id="add-itemName" required name="itemName">
                                            <div class="invalid-feedback">
                                                Please fill in item name
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="add-iDescription" class="col-form-label">Item Description
                                                <span style="color: red">*</span></label>
                                            <textarea class="form-control" id="add-iDescription" required name="iDescription"></textarea>
                                            <div class="invalid-feedback">
                                                Please fill in item description
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="add-stockQty" class="col-form-label">Stock Quantity
                                                <span style="color: red">*</span></label>
                                            <input class="form-control" type="number" min="0" value="0" id="add-stockQty" name="stockQty" required>
                                            <div class="invalid-feedback">
                                                Please fill in the correct stock quantity
                                            </div>
                                        </div>
                                        <div>
                                            <label for="add-itemPrice" class="col-form-label">Price
                                                <span style="color: red">*</span></label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text">$</span>
                                                <input type="number" min="0" value="0" required class="form-control" id="add-itemPrice" name="itemPrice">
                                                <div class="invalid-feedback">
                                                    Please enter the correct amount
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <p class="mt-4"><span style="color: red">*</span> Required</p>
                                            <button id="form_submit" type="submit" class="btn btn-rounded btn-primary">
                                                Submit
                                            </button>
                                            <button id="form_submit" type="reset" class="btn btn-rounded btn-outline-secondary m-1">
                                                Reset
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--edit item-->
    <div class="modal fade" id="edit-Item" tabindex="-1" aria-labelledby="Order Item" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editItem" class="needs-validation" novalidate data-ss-itemid="0000">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="itemName" class="col-form-label">Item Name
                                <span style="color: red">*</span></label>
                            <input class="form-control" type="text" id="itemName" required name="itemName">
                            <div class="invalid-feedback">
                                Please fill in item name
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="iDescription" class="col-form-label">Item Description
                                <span style="color: red">*</span></label>
                            <textarea class="form-control" id="iDescription" required name="iDescription"></textarea>
                            <div class="invalid-feedback">
                                Please fill in item description
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="stockQty" class="col-form-label">Stock Quantity
                                <span style="color: red">*</span></label>
                            <input class="form-control" type="number" min="0" value="0" id="stockQty" name="stockQty" required>
                            <div class="invalid-feedback">
                                Please fill in the correct stock quantity
                            </div>
                        </div>
                        <div>
                            <label for="itemPrice" class="col-form-label">Price
                                <span style="color: red">*</span></label>
                            <div class="input-group mb-3">
                                <span class="input-group-text">$</span>
                                <input type="number" min="0" value="0" required class="form-control" id="itemPrice" name="itemPrice">
                                <div class="invalid-feedback">
                                    Please enter the correct amount
                                </div>
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
        const load_script = ["./assets/js/page/edit-item.js"];
    </script>

<?php
require_once "function/footer.inc.php";