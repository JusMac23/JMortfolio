<!-- addticket.php -->
<?php
session_start(); 

$userlogged = isset($_SESSION['role']) ? $_SESSION['role'] : null;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require 'fpdf/fpdf.php';

include_once("connections/connection.php");
$con = connection();

date_default_timezone_set('Asia/Manila');
$today = date('Y-m-d H:i:s');

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp-relay.brevo.com';
    $mail->SMTPAuth = true;
    $mail->Username = '7e620f002@smtp-brevo.com';
    $mail->Password = 'KXxHA14Fk2WcTpMS';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->setFrom('j_macarayan@cda.gov.ph', 'Helpdesk System');

    if (isset($_POST['submit'])) {
        // Collect form data
        $firstname = mysqli_real_escape_string($con, $_POST['firstname']);
        $lastname = mysqli_real_escape_string($con, $_POST['lastname']);
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $division = mysqli_real_escape_string($con, $_POST['division']);
        $it_area = mysqli_real_escape_string($con, $_POST['it_area']);
        $device = mysqli_real_escape_string($con, $_POST['device']);
        $service = mysqli_real_escape_string($con, $_POST['service']);
        $request = mysqli_real_escape_string($con, $_POST['request']);
        $it_personnel = mysqli_real_escape_string($con, $_POST['it_personnel']);
        $it_email = mysqli_real_escape_string($con, $_POST['it_email']);
        $status = "Pending";

        // Insert ticket into the database
        $sql = "INSERT INTO tickets_tbl (firstname, lastname, status, date_created, division, it_area, email, device, service, request, it_personnel, it_email)
                VALUES ('$firstname', '$lastname', '$status', '$today', '$division', '$it_area', '$email', '$device', '$service', '$request', '$it_personnel', '$it_email')";

        if ($con->query($sql)) {
            $ticket_id = $con->insert_id;

            // Prepare data for PDF generation
            $pdf_data = [
                'division' => $division,
                'date_created' => $today,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'request_number' => $ticket_id,
                'device' => $device,
                'service' => $service,
                'request' => $request,
                'it_personnel' => $it_personnel,
                'action_taken' => 'Pending'
            ];

            // Generate PDF
            function generatePDF($data) {
                $pdf = new FPDF();
                $pdf->AddPage();
                    
                $pdf->SetFont('Arial', '', 8);

                $pageWidth = $pdf->GetPageWidth();
                // Logo/Codedform/Agency Name and Address
                $pdf->Image('icons/cdalogo.png', 10, 25, 25); 
                $pdf->Image('icons/codedform.png', $pageWidth - 40, 10, 30); 

                $pdf->SetXY(0, 30); 
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(0, 5, 'COOPERATIVE DEVELOPMENT AUTHORITY', 0, 1, 'C');

                $pdf->SetXY(0, 35);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(0, 5, 'HEAD OFFICE', 0, 1, 'C');

                $pdf->SetFont('Arial', '', 8);
                $pdf->SetXY(0, 40);
                $pdf->Cell(0, 5, '827 Aurora Blvd., Service Road, Brgy. Immaculate Conception Cubao,', 0, 1, 'C');

                $pdf->SetFont('Arial', '', 8);
                $pdf->SetXY(0, 45);
                $pdf->Cell(0, 5, '1111 Quezon City, Philippines', 0, 1, 'C');

                $pdf->Ln(10); 
                    
                // Department Name
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->Cell(0, 6, 'INFORMATION AND COMMUNICATIONS TECHNOLOGY (ICT) OFFICE', 0, 1, 'C');
                $pdf->SetFont('Arial', 'B', 14); 
                $pdf->Cell(0, 8, 'TECHNICAL SUPPORT ASSISTANCE REQUEST (TSAR) FORM', 0, 1, 'C');

                $pdf->Ln(8);

                // Division/Unit/Section and Date Requested (Row Layout)
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell(95, 7, 'Division/Unit/Section:', 'LTR', 0); 
                $pdf->Cell(95, 7, 'Date Request:', 'LTR', 1);                
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(95, 10, $data['division'], 'LR', 0); 
                $pdf->Cell(95, 10, $data['date_created'], 'LR', 1);                                       
                $pdf->Ln(0);

                // Employee Name (Single Row)
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell(190, 7, 'Employee Name:', 'LTR', 1);
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(190, 10, $data['firstname'] . ' ' . $data['lastname'], 'LRB', 1);
                $pdf->Ln(0);

                // Email and Request Number (Row Layout)
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell(95, 7, 'Email Address:', 'LTR', 0); 
                $pdf->Cell(95, 7, 'Request Number:', 'LTR', 1); 
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(95, 10, $data['email'], 'LRB', 0);  
                $pdf->Cell(95, 10, $data['request_number'], 'LRB', 1);                      
                $pdf->Ln(0);

                // Equipment Repairs and Technical Support Services (Row Layout)
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell(95, 7, 'Equipment Repairs:', 'LTR', 0); 
                $pdf->Cell(95, 7, 'Technical Support Services:', 'LTR', 1);                
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(95, 21, $data['device'], 'LRB', 0);  
                $pdf->Cell(95, 21, $data['service'], 'LRB', 1);                      
                $pdf->Ln(0);

                // Technical Support Request Description (Single Row)
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell(190, 7, 'Technical Support Request Description/Definition:', 'LTR', 1); 
                $pdf->SetFont('Arial', '', 8);
                $pdf->MultiCell(190, 21, $data['request'], 'LRB');
                $pdf->Ln(0);

                // Action Taken
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell(190, 7, 'Action Taken/Recommendation:', 'LTR', 1);               
                $pdf->SetFont('Arial', '', 8);
                $pdf->MultiCell(190, 21, $data['action'], 'LRB');
                $pdf->Ln(14);

                // Signatures (Row Layout)
                $pdf->SetFont('Arial', 'B', 10); 
                $pdf->Cell(95, 7, strtoupper($data['firstname'] . ' ' . $data['lastname']), 0, 0, 'C'); 
                $pdf->Cell(95, 7, strtoupper($data['it_personnel']), 0, 1, 'C'); 
                $pdf->SetFont('Arial', '', 8); 
                $pdf->Cell(95, 7, 'Name/Signature of Responsible Staff/Date', 0, 0, 'C'); 
                $pdf->Cell(95, 7, 'Name/Signature of ICT Personnel/Date', 0, 1, 'C');

                // Generate and save the PDF file
                $pdf_filename = 'TSAR Form_' . time() . '.pdf';
                $pdf->Output('F', $pdf_filename);
                return $pdf_filename;
                }

                $pdf_filename = generatePDF($pdf_data);

                // Send email with the generated PDF attached
                $mail->addAddress($it_email);
                $mail->Subject = 'New Ticket Request from' . ' ' . $firstname . ' ' . $lastname;
                $mail->Body = 'A new ticket has been requested. Please see the attached PDF for details.';
                $mail->addAttachment($pdf_filename);


                if ($mail->send()) {

                    $userlogged = $_SESSION['role'] ?? '0'; 

                    if ($userlogged == 'admin') {
                        echo "<script>alert('Request created and email sent to assigned IT Personnel.'); window.location.href='admin-ticket.php';</script>";

                    } elseif ($userlogged == 'user') {
                        echo "<script>alert('Request created and email sent to assigned IT Personnel.'); window.location.href='icts-ticket.php';</script>";

                    } else {
                        echo "<script>alert('Request created and email sent to assigned IT Personnel.'); window.location.href='index.php';</script>";
                    }
                } else {
                    echo "<script>alert('Request created but email not sent.');</script>";
                }

                unlink($pdf_filename); // Delete PDF file
            } else {
                echo "<script>alert('Failed to create ticket.');</script>";
            }
        }
    } catch (Exception $e) {
        echo "<script>alert('Message could not be sent. Error: {$mail->ErrorInfo}');</script>";
    }
    // Map IT Areas to Personnel
    $it_area_personnel_map = [
        "CDA HO" => ["RONALDO G. RIVERA", "BONIFACIO GARCIA", "ROMAINE NINO TALUCOD", "CARLITO BUAN", "JOSEPH RAINER ROSARIAL", "JESS AGNES", "JUSTINE MACARAYAN"],
        "CDA NCR" => ["PERLITA SOLIS", "DENZEL COLLADO"],
        "CDA CAR" => ["MARK ERICK MENZI"],
        "CDA R1" => ["NOEL ROYUPA", "MANFRED ZULUETA"],
        "CDA R2" => ["JUANA MARIE TENORIO"],
        "CDA R3" => ["RALPH RENDELL TOLEDO", "JASPER JOHN SANTOS"],
        "CDA R4A" => ["LORIELIE PAPA", "MR. BRYAN CARANDANG"],
        "CDA R4B" => ["CRISTIAN DE ADE", "RONEL KAW"],
        "CDA R5" => ["MAY CELESTINE NERY", "MARICEL REGALADO"],
        "CDA R6" => ["JONILYN VESTIDAS", "JOHN KYLE JOSEPH REYES"],
        "CDA R7" => ["MARIEFEL TAGHOY"],
        "CDA R8" => ["VERYAN ARTATES", "LYNDON BROZ TONELETE"],
        "CDA R9" => ["RAUL ALCORAN, JR.", "ESNIBON DASDAS"],
        "CDA R10" => ["MA. ISADEL ATRIGENIO", "GIAN ERCO CAYANONG"],
        "CDA R11" => ["PARALUMAN SEPULVEDA", "MAE ANGELIE ABADIEZ"],
        "CDA R12" => ["RICEL MAE MARTE", "MICHAEL DIMAPUNONG, JR."],
        "CDA R13" => ["SHEMA MICHAL LUBRIO"],
        "CDA R14" => ["JOHN CEDRICK BERMEJO"],
    ];

    // Map Personnel to Emails
    $personnel_email_map = [
        "RONALDO G. RIVERA" => "r_rivera@cda.gov.ph",
        "BONIFACIO GARCIA" => "b_garcia@cda.gov.ph",
        "ROMAINE NINO TALUCOD" => "r_talucod@cda.gov.ph",
        "CARLITO BUAN" => "c_buan@cda.gov.ph",
        "JOSEPH RAINER ROSARIAL" => "jr_rosarial@cda.gov.ph",
        "JESS AGNES" => "j_agnes@cda.gov.ph",
        "JUSTINE MACARAYAN" => "j_macarayan@cda.gov.ph",
        "PERLITA SOLIS" => "ncr@cda.gov.ph",
        "DENZEL COLLADO" => "videocom@cda.gov.ph",
        "MARK ERICK MENZI" => "car@cda.gov.ph",
        "NOEL ROYUPA" => "r1@cda.gov.ph",
        "MANFRED ZULUETA" => "r1@cda.gov.ph",
        "JUANA MARIE TENORIO" => "r2@cda.gov.ph",
        "RALPH RENDELL TOLEDO" => "r3@cda.gov.ph",
        "JASPER JOHN SANTOS" => "r3@cda.gov.ph",
        "LORIELIE PAPA" => "r4a@cda.gov.ph",
        "MR. BRYAN CARANDANG" => "r4a@cda.gov.ph",
        "CRISTIAN DE ADE" => "r4b@cda.gov.ph",
        "RONEL KAW" => "r4b@cda.gov.ph",
        "MAY CELESTINE NERY" => "r5@cda.gov.ph",
        "MARICEL REGALADO" => "r5@cda.gov.ph",
        "JONILYN VESTIDAS" => "r6@cda.gov.ph",
        "JOHN KYLE JOSEPH REYES" => "r6@cda.gov.ph",
        "MARIEFEL TAGHOY" => "r7@cda.gov.ph",
        "VERYAN ARTATES" => "r8@cda.gov.ph",
        "LYNDON BROZ TONELETE" => "r8@cda.gov.ph",
        "RAUL ALCORAN, JR." => "r9@cda.gov.ph",
        "ESNIBON DASDAS" => "r9@cda.gov.ph",
        "MA. ISADEL ATRIGENIO" => "r10@cda.gov.ph",
        "GIAN ERCO CAYANONG" => "r10@cda.gov.ph",
        "PARALUMAN SEPULVEDA" => "r11@cda.gov.ph",
        "MAE ANGELIE ABADIEZ" => "r11@cda.gov.ph",
        "RICEL MAE MARTE" => "r12@cda.gov.ph",
        "MICHAEL DIMAPUNONG, JR." => "r12@cda.gov.ph",
        "SHEMA MICHAL LUBRIO" => "r13@cda.gov.ph",
        "JOHN CEDRICK BERMEJO" => "r13@cda.gov.ph",
    ];

$con->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Ticket</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="icons/cdalogo.png" type="image/png">
    <link rel="stylesheet" href="css/add-style.css">
</head>
<body>

<div class="form-container">
    <span class="close" onclick="redirectToPage()">&times;</span>
        <h2>Ticket Form</h2>
        <form action="add-ticket.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="firstname">First Name:</label>
                    <input type="text" id="firstname" name="firstname" placeholder="Enter your firstname." required>
                </div>

                <div class="form-group">
                    <label for="lastname">Last Name:</label>
                    <input type="text" id="lastname" name="lastname" placeholder="Enter your lastname." required>
                </div>

                <div class="form-group">
                    <label for="division">Division:</label>
                        <select id="status" name="division" required>
                            <option value="OC">Office of the Chairman</option>
                            <option value="OFAD">Office of the Administrator</option>
                            <option value="BOD Asec. Paradillo">Office of Asec. Paradillo</option>
                            <option value="BOD Asec. Hilario">Office of Asec. Hilario</option>
                            <option value="BOD Asec. Yringco">Office of Asec. Yringco</option>
                            <option value="BOD Asec. Lazaga">Office of Asec. Lazaga</option>
                            <option value="BOD Asec. Guinomla">Office of Asec. Guinomla</option>
                            <option value="BOD Asec. Disimban">Office of Asec. Disimban</option>
                            <option value="Board Secretary">Office of the Board Secretary</option>
                            <option value="IAD">Internal Audit Division</option>
                            <option value="Finance">Finance Division / Section</option>
                            <option value="GASS-DA">GASS-Deputy Administrator</option>
                            <option value="Planning">Planning and Policy Development Division / Section</option>
                            <option value="HR">Human Resource Division / Section</option>
                            <option value="Records">Records Section</option>
                            <option value="Cash">Cash Section</option>
                            <option value="Admin">Admin Division / Section</option>
                            <option value="procurement">Procurement</option>
                            <option value="Property Custodian">Property Custodian</option>
                            <option value="ICTD/S">ICTD/S</option>
                            <option value="COA">COA</option>
                            <option value="RSES-DA">RSES-Deputy Administrator</option>
                            <option value="Registration">Registration Division / Section</option>
                            <option value="SED/S">Supervision and Examination Division / Section</option>
                            <option value="LAS">Legal Affairs Service</option>
                            <option value="Adjudication">Adjudication</option>
                            <option value="Legal">Legal</option>
                            <option value="CSF-DA">CSF-Deputy Administrator</option>
                            <option value="CSF-TAD/S">CSF-Technical Assistance Division / Section</option>
                            <option value="CSF-IED/S">CSF-Inspection and Examination Division / Section</option>
                            <option value="IDS-DA">IDS-Deputy Administrator</option>
                            <option value="CPDAD/S">CPDAD/S</option>
                            <option value="CRITD/S">CRITD/S</option>
                        </select>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>

                <?php
                date_default_timezone_set('Asia/Manila'); 
                $today = date('Y-m-d\TH:i');
                ?>
                <div class="form-group">
                    <label for="date_created">Date Created:</label>
                    <input type="datetime-local" id="date_created" name="date_created" value="<?php echo $today; ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="status">Status:</label>
                    <input type="text" name="status" value="Pending" readonly>
                </div>

                <div class="form-group">
                    <label for="device">Device:</label>
                        <select id="device" name="device" required>
                            <option value="Desktop PC">Desktop PC</option>
                            <option value="Laptop/Tablet">Laptop/Tablet</option>
                            <option value="Printer">Printer</option>
                            <option value="Scanner">Scanner</option>
                            <option value="Mobile Phone">Mobile Phone</option>
                            <option value="Others">Others</option>
                        </select>
                </div>

                <div class="form-group">
                    <label for="service">Service:</label>
                        <select id="service" name="service" required>
                            <option value="Equipment Troubleshooting/Repair">Equipment Troubleshooting/Repair</option>
                            <option value="Equipment Setup and Installation">Equipment Setup and Installation</option>
                            <option value="Application Installation/Configuration/Repair">Application Installation/Configuration/Repair</option>
                            <option value="Network and Internet Troubleshooting">Network and Internet Troubleshooting</option>
                            <option value="Network Installation/Re-installation">Network Installation/Re-installation</option>
                            <option value="Virus and Malware Removals">Virus and Malware Removals</option>
                            <option value="Active Directory Registration">Active Directory Registration</option>
                            <option value="aActive Directory Reset Password">Active Directory Reset Password</option>
                            <option value="Govmail Registration">Govmail Registration</option>
                            <option value="Govmail Reset Password">Govmail Reset Password</option>
                            <option value="Client-Backup Assistance">Client-Backup Assistance</option>
                            <option value="Online Assistance">Online Assistance</option>
                            <option value="Account Password Reset">Account Password Reset</option>
                            <option value="Database Assistance">Database Assistance</option>
                        </select>
                </div>

                <div class="form-group">
                    <label for="request">Request:</label>
                    <textarea id="request" name="request" rows="4" placeholder="Please specify your request."required></textarea>
                </div>

                <div class="form-group">
                    <label for="action">Action:</label>
                    <textarea id="action" name="action" rows="4" placeholder="For IT Personnel only."readonly></textarea>
                </div>

                <div class="form-group">
                    <label for="photo">Photo: (Optional)</label>
                    <input type="file" id="photo" name="photo" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="it_area">IT Area:</label>
                    <select id="it_area" name="it_area" required>
                        <option value="" disabled selected>Select IT Area</option> 
                        <?php foreach (array_keys($it_area_personnel_map) as $area) : ?>
                            <option value="<?php echo htmlspecialchars($area); ?>"><?php echo htmlspecialchars($area); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                    <div class="form-group">
                        <label for="it_personnel">IT Personnel:</label>
                        <select id="it_personnel" name="it_personnel" required>
                            <option value="">Select Personnel</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="it_email">IT Email:</label>
                        <input type="email" id="it_email" name="it_email" value="" readonly required>
                    </div>

                <div class="button-container">
                <button type="submit" name="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
<script>

function redirectToPage() {

    var userLoggedIn = "<?php echo isset($_SESSION['role']) ? $_SESSION['role'] : ''; ?>";
    if (userLoggedIn === "admin") {
        window.location.href = "admin-ticket.php";
    } else if (userLoggedIn === "user") {
        window.location.href = "icts-ticket.php";
    } else {
        window.location.href = "index.php";
    }
}


    document.getElementById('it_area').addEventListener('change', function () {
            const itArea = this.value;
            const personnelSelect = document.getElementById('it_personnel');
            const emailInput = document.getElementById('it_email');
            
            // Clear previous options and email
            personnelSelect.innerHTML = '<option value="">Select Personnel</option>';
            emailInput.value = '';

            // Populate personnel based on selected IT Area
            const areaPersonnelMap = <?php echo json_encode($it_area_personnel_map); ?>;
            const personnelEmailMap = <?php echo json_encode($personnel_email_map); ?>;

            if (itArea in areaPersonnelMap) {
                areaPersonnelMap[itArea].forEach(function (personnel) {
                    const option = document.createElement('option');
                    option.value = personnel;
                    option.textContent = personnel;
                    personnelSelect.appendChild(option);
                });
            }

            // Set email based on selected personnel
            personnelSelect.addEventListener('change', function () {
                const selectedPersonnel = this.value;
                emailInput.value = personnelEmailMap[selectedPersonnel] || '';
            });
        });
</script>
</body>
</html>
