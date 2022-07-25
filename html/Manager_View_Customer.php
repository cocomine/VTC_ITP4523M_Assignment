<?php
$conn = null;
$title = "View Customer";
require_once "function/head.inc.php";

//check is manger
if ($_SESSION['position'] != 1) {
    if ($_SERVER['REQUEST_METHOD'] == "POST") http_response_code(403);
    else header("location: " . $_SERVER['HTTP_REFERER']);
}

//is post request
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    ob_clean();
    header("Content-Type: text/json");

    if (isset($_GET['delete-customer'])) {
        //is delete customer
        if (!empty($_GET['delete-customer'])) {
            //SANITIZE
            $email = filter_var($_GET['delete-customer'], FILTER_SANITIZE_EMAIL);

            try {
                //delete order item
                $stmt = $conn->prepare("SELECT orderID FROM orders WHERE customerEmail = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $stmt = $conn->prepare("DELETE FROM itemorders WHERE orderID = ?");
                    $stmt->bind_param("s", $row['orderID']);
                    $stmt->execute();
                }

                //delete order
                $stmt = $conn->prepare("DELETE FROM orders WHERE customerEmail = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();

                //delete customer
                $stmt = $conn->prepare("DELETE FROM customer WHERE customerEmail = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();

                echo json_encode(array("result" => "Success")); //is deleted
            } catch (mysqli_sql_exception $e) {
                echo json_encode(array("result" => "Fail"));
            }
        } else {
            http_response_code(400); //is empty
        }

    } elseif (isset($_GET['get-customer'])) {
        //is get all customer
        $stmt = $conn->prepare("SELECT * FROM customer");
        $stmt->execute();
        $result = $stmt->get_result();

        //get data
        $output = array();
        while ($row = $result->fetch_assoc()) {
            $output[] = array(
                "Name" => $row['customerName'],
                "Email" => $row['customerEmail'],
                "Phone" => $row['phoneNumber']
            );
        }

        echo json_encode($output); //output
    } else {
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

                        <li>
                            <a href="javascript:void(0)" aria-expanded="false">
                                <span>Item</span></a>
                            <ul class="collapse">
                                <li><a href="view-item.php">View Item</a></li>
                                <li><a href="Manger_Insert_and_Edit_Item.php">Insert and Edit Item</a></li>
                            </ul>
                        </li>

                        <li class="active">
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
                    <h4 class="page-title pull-left">View Customer</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a>View Customer</a></li>
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
                <!--Customer List Start-->
                <div class="col-12 mt-5">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title">Customer List</h4>
                            <div class="data-tables datatable-dark">
                                <table id="dataTable" class="text-center">
                                    <thead class="text-capitalize">
                                    <tr>
                                        <th>Customer's Name</th>
                                        <th>Customer's Email</th>
                                        <th>Customer's Phone</th>
                                        <th>Delete</th>
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

    <script>
        const load_script = ["./assets/js/page/view-customer.js"];
    </script>
<?php
require_once "function/footer.inc.php";