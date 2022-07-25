<?php
$conn = null;
$title = "View Item";
require_once "function/head.inc.php";

//check is manger
if($_SESSION['position'] != 1){
    header("location: ".$_SERVER['HTTP_REFERER']);
}

//is post request
if($_SERVER['REQUEST_METHOD'] == "POST"){
    ob_clean();
    header("Content-Type: text/json");

    //is get all item
    if (isset($_GET['get-item'])){
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
                            <a href="javascript:void(0)">
                                <span>Item</span></a>
                            <ul class="collapse">
                                <li class="active"><a href="view-item.php">View Item</a></li>
                                <li><a href="Manger_Insert_and_Edit_Item.php">Insert and Edit Item</a></li>
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
                    <h4 class="page-title pull-left">View Item</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a href="view-item.php">Item</a></li>
                        <li><span>View Item</span></li>
                    </ul>
                </div>
                <!--User Profile-->
                <div class="col-md-6 col-sm-4 clearfix">
                    <ul class="notification-area pull-right">
                        <ul class="user-profile pull-right">
                            <h4 class="user-name dropdown-toggle" data-bs-toggle="dropdown" id="username" aria-expanded="false"><?php echo $_SESSION['staffName']?>
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
                                        <th>Price</th>
                                        <th>Item Description</th>
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
        const load_script = ["./assets/js/page/view-item.js"];
    </script>
<?php
require_once "function/footer.inc.php";