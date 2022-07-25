<?php
$conn = null;
$title = "Generate Report";
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

    if (isset($_GET['report-date'])) {
        //is get report
        if (!empty($_GET['report-date'])) {
            //SANITIZE
            $date = explode("-", $_GET['report-date']);
            $month = filter_var($date[0], FILTER_SANITIZE_NUMBER_INT);
            $year = filter_var($date[1], FILTER_SANITIZE_NUMBER_INT);

            try {
                $stmt = $conn->prepare("SELECT s.staffID, s.staffName, ? AS 'YEAR', ? AS 'MONTH', 
                        (SELECT COUNT(o.orderID) FROM orders o WHERE o.staffID = s.staffID AND YEAR(o.dateTime) = YEAR AND MONTH(o.dateTime) = MONTH GROUP BY o.staffID) AS 'orders', 
                        (SELECT SUM(o.totalPrice) FROM orders o WHERE o.staffID = s.staffID AND YEAR(o.dateTime) = YEAR AND MONTH(o.dateTime) = MONTH GROUP BY o.staffID) AS 'total'
                        FROM staff s;");
                $stmt->bind_param("ss", $year, $month);
                $stmt->execute();
                $result = $stmt->get_result();

                //get data
                $output = array();
                while ($row = $result->fetch_assoc()) {
                    $output[] = array(
                        "StaffID" => $row['staffID'],
                        "Name" => $row['staffName'],
                        "Orders" => $row['orders'],
                        "Total" => $row['total']
                    );
                }

                echo json_encode($output); //output
            } catch (mysqli_sql_exception $e) {
                http_response_code(400);
            }
        } else {
            http_response_code(400); //is empty
        }
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

                        <li>
                            <a href="Manager_View_Customer.php"><span>View Customer</span></a>
                        </li>

                        <li class="active">
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
                    <h4 class="page-title pull-left">Generate Report</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a>Generate Report</a></li>
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
                <div class="row mt-5 mb-5">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-sm-flex justify-content-between align-items-center">
                                    <h4 class="header-title mb-0">Monthly Sales Record</h4>
                                    <div class="form-group" style="font-size: 1.5em">
                                        <span id="last" data-ss-action="last" title="Last month"><span class="ti-angle-left"></span></span>&nbsp;
                                        <span id="showMonth">5 / 2022</span>&nbsp;
                                        <span id="next" data-ss-action="next" title="Next month"><span class="ti-angle-right"></span></span>
                                    </div>
                                </div>
                                <div class="trad-history mt-4">
                                    <div class="tab-content">
                                        <div class="active" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="dbkit-table" id="monReport">
                                                    <thead>
                                                        <tr>
                                                            <th>Staff ID</th>
                                                            <th>Staff Name</th>
                                                            <th>Number Of Order</th>
                                                            <th>Total Sales Amount</th>
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
                </div>
            </div>
        </div>
    </div>

    <script>
        const load_script = ["./assets/js/page/report.js"];
    </script>

<?php
require_once "function/footer.inc.php";