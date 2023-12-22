<?php
//
//      This site will accept an Offercode & Description and generate a ShortCode. If a ShortCode is given in the querystring
//      the original Offercode & Description will be pulled from the database and displayed.
//
//error_reporting(E_ALL);         // debug & development only
//ini_set('display_errors','1');  // debug & development only

include "dbconfig.php";
include "library.php";

$offer_code = "";
$description = "";
$short_code = "";
$html_offer_summary = "";
$activeTab = "getOfferShortCode";

$short_code = isset($_GET['short_code']) ? sanitize_user_input($_GET['short_code']) : "";

if(strlen($short_code) > 0)
{
    $activeTab = "viewOffer";
    $conn = new mysqli(HOST, USER, PASS, DBNAME);
    if ($conn->connect_error)
    {
        die("Connection failed: " . $conn->connect_error);

    } else {
        
        $query = "SELECT offer_code, description FROM offer_codes WHERE short_code = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $short_code);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) 
        {
            while ($row = $result->fetch_assoc()) 
            {
                $offer_code = $row["offer_code"];
                $description = $row["description"];
            }

            $offer_code_hash = hash('sha256', $offer_code, true);
            $base58EncodedHash = base58_encode($offer_code_hash);
            $url = "https://api.dexie.space/v1/offers/$base58EncodedHash";

            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

            $response = curl_exec($ch);
            curl_close($ch);
            
            if ($response === false) {
                echo "cURL Error: " . curl_error($ch);
                die();
            }
            
            $response = json_decode($response, true); // `true` converts objects to associative arrays

            if ($response['success']) {

                $status = isset($response['offer']['status']) ? $response['offer']['status'] : "";
                $dateFound = isset($response['offer']['date_found']) ? $response['offer']['date_found'] : "";
                $completed = isset($response['offer']['date_completed']) ? $response['offer']['date_completed'] : "";
                $pending = isset($response['offer']['date_pending']) ? $response['offer']['date_pending'] : "";
                $price = isset($response['offer']['price']) ? $response['offer']['price'] : "";
            
                switch($status)
                {
                    case 0: $status_name = "Active"; $status_date = $dateFound; break;
                    case 1: $status_name = "Pending"; $status_date = $pending; break;
                    case 2: $status_name = "Cancelling"; $status_date = $dateFound; break;
                    case 3: $status_name = "Cancelled"; $status_date = $dateFound; break;
                    case 4: $status_name = "Completed"; $status_date = $completed; break;
                    case 5: $status_name = "Unknown"; $status_date = $dateFound; break;
                    case 6: $status_name = "Expired"; $status_date = $expiry; break;
                    default: $status_name = ""; break;
                }
                $offered = isset($response['offer']['offered']) ? $response['offer']['offered'] : "";
                $requested = isset($response['offer']['requested']) ? $response['offer']['requested'] : "";
            
                $html_offer = "";
                foreach ($offered as $offer) {
                    if(isset($offer['is_nft']) && $offer['is_nft'] == true)
                    {
                        $html_offer .= "<div class='offer_div'><img class='offer_img' src='" . $offer['preview']['tiny'] . "'><br>" . $offer['name'] . "<br>" . $offer['collection']['name'] . "</div>";
                    } else {
                        $offerId = $offer['id'];
                        $offerCode = ($offer['code'] !== null ? $offer['code'] : "");
                        $offerName = $offer['name'];
                        $offerAmount = $offer['amount'];
                        $html_offer .= "<br>" . $offerAmount . " " . (strlen($offerCode) == 0 ? $offerName : $offerCode) . "<br>";
                    }
                }
            
                $html_request = "";
                foreach ($requested as $request) {
                    $requestId = $request['id'];
                    $requestCode = $request['code'];
                    $requestName = $request['name'];
                    $requestAmount = $request['amount'];
                    $html_request .= $requestAmount . " " . $requestCode .  "<br>";
                }
            
                $html_offer_summary .= "<table id='offer_summary'>";
                $html_offer_summary .= "<tr><th colspan=2>Offer Details</th></tr>";
                $html_offer_summary .= "<tr><td>Status</td><td>$status_name</td></tr>";
                $html_offer_summary .= "<tr><td>Date</td><td>$status_date</td></tr>";
                $html_offer_summary .= "<tr><td>Price</td><td>$price</td></tr>";
                $html_offer_summary .= "<tr><th><br>Offer</th><th><br>Requested</th></tr>";
                $html_offer_summary .= "<tr><td>$html_offer</td><td>$html_request</td></tr>";
                $html_offer_summary .= "</table><br><br>";
                
            }

        } else {
            $offer_code = "";
            $description = "";
        }
        $conn->close();

    }

} elseif( $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['offerCode']) && $_POST['offerCode'] != "")
{
    // Lets get the Offer & Description, then generate a short code and store all in the DB.
    // then we need to redirect back to the page using the shortcode as a querystring
    $conn = new mysqli(HOST, USER, PASS, DBNAME);
    if ($conn->connect_error)
    {
        //die("Connection failed: " . $conn->connect_error);
    }
    do {
        $unique = true;
        $short_code = generateRandomString(); // Generate a random string
    
        // SQL query to check if the string exists in the database
        $query = "SELECT COUNT(*) FROM offer_codes WHERE short_code = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $short_code); // 's' specifies the variable type => 'string'
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
    
        if ($row['COUNT(*)'] > 0) {
            $unique = false; // String exists, set $unique to false
        }
    } while (!$unique);

    $offer_code = isset($_POST['offerCode']) ? sanitize_user_input($_POST['offerCode']) : '';
    $description = isset($_POST['description']) ? sanitize_user_input($_POST['description']) : '';

    $stmt = $conn->prepare("INSERT INTO offer_codes (offer_code, description, short_code) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $offer_code, $description, $short_code);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    header('Location: index.php?short_code=' . urlencode($short_code));
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <title>Offer ShortCode Generator</title>
    <script>
        function copyToClipboard(elementId) {
            var copyText = document.getElementById(elementId);
            copyText.select(); // Select the text
            copyText.setSelectionRange(0, 99999); // For mobile devices
            document.execCommand("copy"); // Copy the text
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">

        <!-- Home Button -->
        <div class="text-left mt-3 mb-3">
            <a href="index.php" class="btn btn-success"><i class="fas fa-home"></i> Home</a>
        </div>

        <!-- Banner -->
        <div class="banner-container">
            <img src="banner.png" alt="Banner" class="banner-image w-100"/>
            <span id="experiment" alt="An Experiment" class="experiment-text">An Experiment</span>
        </div>

        <br><br>

        <!-- Nav tabs -->
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link overwrite-tab-color <?php echo ($activeTab == 'getOfferShortCode' ? 'active' : ''); ?>" data-toggle="tab" href="#getOfferShortCode">Get Offer Short Code</a>
            </li>
            <li class="nav-item">
                <a class="nav-link overwrite-tab-color <?php echo ($activeTab == 'viewOffer' ? 'active' : ''); ?>" data-toggle="tab" href="#viewOffer">View Offer</a>
            </li>
            <li class="nav-item">
                <a class="nav-link overwrite-tab-color" href="index.php">Reset</a>
            </li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">

            <!-- Section 1: Offer Short Code Creation -->
            <div id="getOfferShortCode" class="container tab-pane <?php echo ($activeTab == 'getOfferShortCode' ? 'show active' : ''); ?>"><br>
                <h3>Get Offer Short Code</h3>
                <form action="index.php" method="post">
                    <!-- Offer Code Input -->
                    <div class="form-group">
                        <label for="offerCode">Offer Code:</label>
                        <textarea id="offerCode" name="offerCode" class="form-control" placeholder="Enter full offer code" rows=6 required></textarea>
                    </div>

                    <!-- Description Textbox -->
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" class="form-control" placeholder="Enter description (optional)" rows="4"></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-success">Submit</button>
                </form>
            </div>
        
            <!-- Section 2: View Offer Details -->
            <div id="viewOffer" class="container tab-pane fade <?php echo ($activeTab == 'viewOffer' ? 'show active' : ''); ?>"><br>
                <h3>View Offer Code</h3>
                <form action="index.php" method="get">
                    <div class="form-group">

                        <!-- SHORT CODE -->
                        <div class="form-row">
                            <div class="col">
                                <label for="inputShortCode">Enter Short Code:</label>
                                <input type="text" id="inputShortCode" name="short_code" class="form-control" placeholder="Enter Short Code" value="<?php echo (strlen($short_code) > 0 ? $short_code : ''); ?>">
                            </div>
                            <div class="col-auto d-flex align-items-end">
                                <?php
                                    echo (strlen($short_code) > 0) ? "<button onclick=\"copyToClipboard('inputShortCode')\" class=\"btn btn-secondary\">Copy</button>" : "<button type=\"submit\" class=\"btn btn-success\">Submit</button>";
                                ?>
                            </div>
                        </div>

                        <!-- SHORT CODE URL -->
                        <div class="form-row">
                            <div class="col">
                                <label for="shortCodeURL">Short Code URL:</label>
                                <input type="text" id="shortCodeURL" name="short_code_url" class="form-control" placeholder="Short Code URL" value="<?php echo (strlen($short_code) > 0 ? "https://offerco.de/" . $short_code : ''); ?>">
                            </div>
                            <div class="col-auto d-flex align-items-end">
                                <button onclick="copyToClipboard('shortCodeURL')" class="btn btn-secondary">Copy</button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="offer-details">
                    <div class="form-group">
                        <!-- OFFER CODE -->
                        <div class="form-row">
                            <div class="col">
                                <label for="offerCodeDisplay">Offer Code:</label>
                                <textarea id="offerCodeDisplay" name="offerCode" class="form-control" rows=6 readonly><?php echo ($activeTab == 'viewOffer' ? $offer_code : ''); ?></textarea>
                            </div>
                            <div class="col-auto d-flex align-items-end">
                                <button onclick="copyToClipboard('offerCodeDisplay')" class="btn btn-secondary">Copy</button>
                            </div>
                        </div>
                    </div>

                    <!-- Description Textbox -->
                    <div class="form-group">
                        <!-- DESCRIPTION -->
                        <div class="form-row">
                            <div class="col">
                                <label for="descriptionDisplay">Description:</label>
                                <textarea id="descriptionDisplay" name="description" class="form-control" rows="4" readonly><?php echo ($activeTab == 'viewOffer' ? $description : ''); ?></textarea>
                            </div>
                            <div class="col-auto d-flex align-items-end">
                                <button onclick="copyToClipboard('descriptionDisplay')" class="btn btn-secondary">Copy</button>
                            </div>
                        </div>
                    </div>
                </div>

                <?php echo $html_offer_summary; ?>
            </div>

        </div>

    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
