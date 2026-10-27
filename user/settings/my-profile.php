<?php
include("../../includes/connections.php");
session_start();

if (!empty($_SESSION['logged_in'])) {
    if ($_SESSION['role'] == 'super_admin') {
        header("location:../../user/dashboard/dashboard.php");
    }
} else {
    header("location:../../index.php");
    die;
}

// Initialize variables with default values
$roles = $email = $password = '';
$last_name = $first_name = $phone = $profile = $profile_type = '';
$clientele = $skype = $job_title = $expertise = $gender = '';
$company_name = $address = $city = $state = $zip = $country = '';
$c_phone = $website = $vat_number = $telegram_1 = $telegram_link_1 = '';
$telegram_2 = $telegram_link_2 = $pinterest = $instagram = $github = $tumbir = '';

// Validate session user_id
if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    die("Invalid user session");
}
$user_id = (int)$_SESSION['user_id'];

// Query users table with prepared statement
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $con->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $con->error);
}
$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $roles = $row['role'] ?? '';
        $email = $row['email'] ?? '';
        $password = $row['password'] ?? '';
    }
}
$stmt->close();

// Query users_info table with prepared statement
$queryPf = "SELECT * FROM users_info WHERE user_id = ?";
$stmtPf = $con->prepare($queryPf);
if (!$stmtPf) {
    die("Prepare failed: " . $con->error);
}
$stmtPf->bind_param("i", $user_id);
if (!$stmtPf->execute()) {
    die("Execute failed: " . $stmtPf->error);
}
$resultPf = $stmtPf->get_result();

if ($resultPf->num_rows > 0) {
    while ($rowPf = $resultPf->fetch_assoc()) {
        $last_name = $rowPf['last_name'] ?? '';
        $first_name = $rowPf['first_name'] ?? '';
        $phone = $rowPf['phone'] ?? '';
        $profile = $rowPf['profile'] ?? '';
        $profile_type = $rowPf['profile_type'] ?? '';
        
        if (empty($profile)) {
            $profile = file_get_contents('Default_pfp.png');
        }
        
        $clientele = $rowPf['clientele'] ?? '';
        $skype = $rowPf['skype'] ?? '';
        $job_title = $rowPf['job_title'] ?? '';
        $expertise = $rowPf['expertise'] ?? 0;
        $gender = $rowPf['gender'] ?? '';
        $company_name = $rowPf['company_name'] ?? '';
        $address = $rowPf['address'] ?? '';
        $city = $rowPf['city'] ?? '';
        $state = $rowPf['state'] ?? '';
        $zip = $rowPf['zip'] ?? '';
        $country = $rowPf['country'] ?? '';
        $c_phone = $rowPf['c_phone'] ?? '';
        $website = $rowPf['website'] ?? '';
        $vat_number = $rowPf['vat_number'] ?? '';
        $telegram_1 = $rowPf['telegram_1'] ?? '';
        $telegram_link_1 = $rowPf['telegram_link_1'] ?? '';
        $telegram_2 = $rowPf['telegram_2'] ?? '';
        $telegram_link_2 = $rowPf['telegram_link_2'] ?? '';
        $pinterest = $rowPf['pinterest'] ?? '';
        $instagram = $rowPf['instagram'] ?? '';
        $github = $rowPf['github'] ?? '';
        $tumbir = $rowPf['tumbir'] ?? '';

        // Query ticket_category if expertise exists
        if ($expertise != 0) {
            $queryExpe = "SELECT * FROM ticket_category WHERE category_id = ?";
            $stmtExpe = $con->prepare($queryExpe);
            if ($stmtExpe) {
                $stmtExpe->bind_param("i", $expertise);
                if ($stmtExpe->execute()) {
                    $resultExpe = $stmtExpe->get_result();
                    if ($resultExpe->num_rows > 0) {
                        while ($rowExpe = $resultExpe->fetch_assoc()) {
                            $technician_expertise = $rowExpe['category'] ?? '';
                        }
                    }
                }
                $stmtExpe->close();
            }
        }
    }
}
$stmtPf->close();


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <?php include("../header.php") ?>
    <style>
        .profile {
            height: 200px;
            padding-top: 5px;
            background-color: #071d20;
            color: #fff;
        }

        .nav-item .nav-link {
            font-size: .7rem;
        }

        .nav-item .nav-link {
            padding: 14px;
            position: relative;
            cursor: pointer;
        }


        .nav-item .nav-link::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: #43727a;
            transition: width 0.3s ease;
        }

        .nav-item .nav-link:hover::before,
        .nav-item .nav-link.active::before {
            width: 100%;

        }

        h5 {
            margin: 0;
            font-size: 1rem;
        }

        .card-footer {
            border-top: 0;
            background-color: #fff;
        }

        .card-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding-left: 0;
        }

        .card-body label {
            font-size: .7rem;
            padding-left: 15px;
        }

        .card-body .row {
            padding: 12px 0px;
            border-bottom: 1px dashed rgba(0, 0, 0, 0.1);
        }

        input.form-control-sm.focus-ring-light,
        textarea.form-control-sm.focus-ring-light {
            border: 1px solid transparent;
            font-size: .7rem;
        }

        .profile .btn i {
            font-size: 1.2rem;
        }

        .profile .btn {
            color: #fff;
        }

        .profile .btn:hover {
            color: #c2c2c2;

        }

        .profile-picture img {
            height: 120px;
            width: 120px;
            background-color: transparent;
            border-radius: 50%;
            object-fit: cover;
        }

        .profile span {
            font-size: .8rem;
            letter-spacing: .5px;
            font-weight: 100;
        }

        .badge {
            font-size: .7rem !important;
        }

        span.email,
        span.label,
        span.company,
        span.address {
            font-size: .65rem;
        }


        .social-icons i {
            font-size: 1.7rem;
            color: rgb(140, 136, 102);
            border-radius: 50%;
            padding: 2px;
            border: 1px solid rgb(140, 136, 102);

        }

        .social-icons:hover i {
            color: #fff;
            border: 1px solid #fff;
        }

        .gen-info-social i {
            font-size: 1.2rem;
            line-height: 0;
            color: rgb(140, 136, 102);
        }

        .gen-info-social:hover i {
            color: #fff;
        }


        .invalid-pass {
            color: darkred;
        }

        .valid-pass {
            color: darkslategray;

        }
    </style>

</head>

<body>
    <!-- partial:index.partial.html -->
    <div class="layout has-sidebar fixed-sidebar fixed-header">
        <?php include("../sidebar.php"); ?>

        <div class="layout">
            <main class="content" style="padding:0px 12px">
                <div>
                    <a id="btn-toggle" href="#" class="sidebar-toggler break-point-sm">
                        <i class="ri-menu-line ri-xl"></i>
                    </a>
                </div>


                <div id="my-profile">
                    <!-- form -->
                    <form id="update-my-profile" autocomplete="off" enctype="multipart/form-data">
                        <div class="refresh-my-profile">
                            <div class="row profile d-flex">
                                <div class="col d-flex align-items-start gap-4">
                                    <button type="button" id="updateProfile" class="btn mt-2"><i class="ri-camera-fill"></i></button>
                                    <input name="profile_picture" type="file" id="profile-change" accept="image/*" style="display: none;">
                                    <div class="text-center mt-3" style="max-width:200px">
                                        <div class="profile-picture mb-2">
                                            <?php echo '<img id="profile-image" src="data:' . $profile_type . ';base64,' . base64_encode($profile) . '" alt="profile">'; ?>
                                        </div>
                                        <span><?= $first_name . " " . $last_name ?></span>
                                    </div>
                                    <div class="col-3 mt-4 ps-4">
                                        <div class="d-flex">
                                            <?php
                                            function generateSkypeLink($skype)
                                            {
                                                return "skype:$skype?chat";
                                            }
                                            $skypeLink = generateSkypeLink($skype);
                                            ?>
                                            <span class="badge mb-3 me-2"><?= $job_title ?></span>
                                            <?php if ($phone != "") { ?>
                                                <span class="mb-3 gen-info-social me-1"><a title="<?= $phone ?>" href="tel:<?= $phone ?>"><i class="ri-smartphone-line"></i></a></span>
                                            <?php }
                                            if ($skype != "") { ?>
                                                <span class="mb-3 gen-info-social"><a title="<?= $skype ?>" href="<?= 'skype:' . $skype . '?chat'; ?>"><i class="ri-skype-fill"></i></a></span>
                                            <?php } ?>
                                        </div>
                                        <div>
                                            <span class="email"><i class="ri-mail-line"></i>&nbsp; <?= $email ?></span>
                                        </div>
                                        <div class="mt-3 d-block">
                                            <?php
                                            if ($telegram_link_1 != "") {
                                            ?>
                                                <a title="<?= $telegram_1 ?>" href="<?php if (substr($telegram_link_1, 0, 8) == 'https://') {
                                                                                        echo $telegram_link_1;
                                                                                    } else {
                                                                                        echo 'https://' . $telegram_link_1;
                                                                                    } ?>" target="_blank" class="me-2 social-icons"><i class="ri-telegram-fill"></i></a>
                                            <?php
                                            }
                                            if ($telegram_link_2 != "") {
                                            ?>
                                                <a title="<?= $telegram_2 ?>" href="<?php if (substr($telegram_link_2, 0, 8) == 'https://') {
                                                                                        echo $telegram_link_2;
                                                                                    } else {
                                                                                        echo 'https://' . $telegram_link_2;
                                                                                    } ?>" target="_blank" class="me-2 social-icons"><i class="ri-telegram-fill"></i></a>

                                            <?php
                                            }
                                            if ($pinterest != "") {
                                            ?>
                                                <a title="<?= $pinterest ?>" href="<?php if (substr($pinterest, 0, 8) == 'https://') {
                                                                                        echo $pinterest;
                                                                                    } else {
                                                                                        echo 'https://' . $pinterest;
                                                                                    } ?>" target="_blank" class="me-2 social-icons"><i class="ri-pinterest-fill"></i></a>

                                            <?php
                                            }
                                            if ($instagram != "") {
                                            ?><a title="<?= $instagram ?>" href="<?php if (substr($instagram, 0, 8) == 'https://') {
                                                                                        echo $instagram;
                                                                                    } else {
                                                                                        echo 'https://' . $instagram;
                                                                                    } ?>" target="_blank" class="me-2 social-icons"><i class="ri-instagram-fill"></i></a>

                                            <?php
                                            }
                                            if ($github != "") {
                                            ?><a title="<?= $github ?>" href="<?php if (substr($github, 0, 8) == 'https://') {
                                                                                    echo $github;
                                                                                } else {
                                                                                    echo 'https://' . $github;
                                                                                } ?>" target="_blank" class="me-2 social-icons"><i class="ri-github-fill"></i></a>

                                            <?php
                                            }
                                            if ($tumbir != "") {
                                            ?><a title="<?= $tumbir ?>" href="<?php if (substr($tumbir, 0, 8) == 'https://') {
                                                                                    echo $tumbir;
                                                                                } else {
                                                                                    echo 'https://' . $tumbir;
                                                                                } ?>" target="_blank" class="me-2 social-icons"><i class="ri-tumblr-fill"></i></a>
                                            <?php } ?>
                                        </div>

                                        <?php
                                        if ($roles == 'technician') {
                                        ?>
                                            <div class="mt-2">
                                                <span style="font-size:10px">Expertise : </span><span class="bg bg-success" style="font-size:10px; padding: 2px 6px; border-radius:2px"><?= $technician_expertise ?></span>
                                            </div>
                                        <?php
                                        }
                                        ?>


                                    </div>
                                    <div class="col mt-4 ps-4">
                                        <div class="mb-2">
                                            <?php
                                            if ($company_name != '') {
                                            ?>
                                                <span class="label">Company name:</span>&nbsp;&nbsp;<span class="company"><?= $company_name ?></span>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                        <div>
                                            <span class="address"><?= $address ?></span>
                                        </div>
                                    </div>





                                </div>


                            </div>
                        </div>
                        <div class="row">
                            <nav class="navbar navbar-expand-lg bg-body-secondary" style="padding:0">
                                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                                    <span class="navbar-toggler-icon"></span>
                                </button>
                                <div class="collapse navbar-collapse" id="navbarNav">
                                    <ul class="navbar-nav gap-1">
                                        <li class="nav-item">
                                            <a id="nav-general" class="nav-link active" aria-current="page">General Info</a>
                                        </li>
                                        <li class="nav-item">
                                            <a id="nav-company" class="nav-link">Company</a>
                                        </li>
                                        <li class="nav-item">
                                            <a id="nav-social" class="nav-link">Social Links</a>
                                        </li>
                                        <li class="nav-item">
                                            <a id="nav-account" class="nav-link">Account Settings</a>
                                        </li>
                                    </ul>
                                </div>

                            </nav>
                        </div>

                        <!-- Content -->


                        <!-- General info -->
                        <div class="row tabs" id="div-general">
                            <div class="card" style="border: 0;">
                                <div class="card-header">
                                    <h5>General Info</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row" style="padding-top:0">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>First name</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $first_name ?>" name="first_name" required autocomplete="off" maxlength="50" placeholder="First name" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>Last name</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $last_name ?>" name="last_name" required autocomplete="off" maxlength="50" placeholder="Last name" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>Phone</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $phone ?>" name="phone" autocomplete="off" maxlength="15" placeholder="Phone" class="form-control form-control-sm focus-ring focus-ring-light" type="text" oninput="this.value = this.value.replace(/[^ -+0-9]/g, '')">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>Clientele</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $clientele ?>" name="clientele" required autocomplete="off" maxlength="50" placeholder="" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>Skype</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $skype ?>" name="skype" autocomplete="off" placeholder="Skype" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>Job title</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $job_title ?>" name="job_title" autocomplete="off" placeholder="Job title" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>Gender</label>
                                        </div>
                                        <div class="col d-flex gap-4">
                                            <label class="d-flex align-items-center" style="padding:0">
                                                <input <?php if ($gender == 'male') {
                                                            echo 'checked';
                                                        } ?> type="radio" name="gender" value="male">&nbsp;Male
                                            </label>
                                            <label class="d-flex align-items-center" style="padding:0">
                                                <input <?php if ($gender != 'male') {
                                                            echo 'checked';
                                                        } ?> class="form-radio" type="radio" name="gender" value="female">&nbsp;Female
                                            </label>
                                        </div>
                                    </div>

                                </div>
                                <div class="card-footer">
                                </div>
                            </div>
                        </div>
                        <!-- General info -->



                        <!-- Company -->
                        <div class="row tabs d-none" id="div-company">
                            <div class="card" style="border: 0;">
                                <div class="card-header">
                                    <h5>Client info</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row" style="padding-top:0">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>Company Name</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $company_name ?>" name="company_name" autocomplete="off" placeholder="Company Name" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex align-self-start">
                                            <label>Address</label>
                                        </div>
                                        <div class="col">
                                            <textarea name="address" autocomplete="off" placeholder="Address" cols="30" rows="3" class="form-control form-control-sm focus-ring focus-ring-light"><?= $address ?></textarea>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>City</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $city ?>" name="city" autocomplete="off" placeholder="City" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>State</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $state ?>" name="state" autocomplete="off" placeholder="State" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>Zip</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $zip ?>" name="zip" autocomplete="off" placeholder="Zip" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>Country</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $country ?>" name="country" autocomplete="off" placeholder="Country" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>Phone</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $c_phone ?>" name="c_phone" autocomplete="off" placeholder="Phone" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>Website</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $website ?>" name="website" autocomplete="off" placeholder="Website" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>VAT Number</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $vat_number ?>" name="vat_number" autocomplete="off" placeholder="VAT Number" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>



                                </div>
                                <div class="card-footer">
                                </div>
                            </div>


                        </div>
                        <!-- Company -->


                        <!-- Social Links -->
                        <div class="row tabs d-none" id="div-social">
                            <div class="card" style="border: 0;">
                                <div class="card-header">
                                    <h5>Social Links</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row" style="padding-top:0">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>Telegram 1</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $telegram_1 ?>" name="telegram_1" autocomplete="off" placeholder="Name of Telegram" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>Telegram Link 1</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $telegram_link_1 ?>" name="telegram_link_1" autocomplete="off" placeholder="https://telegram.me/++" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>Telegram 2</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $telegram_2 ?>" name="telegram_2" autocomplete="off" placeholder="Name of Telegram" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>Telegram Link 2</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $telegram_link_2 ?>" name="telegram_link_2" autocomplete="off" placeholder="https://telegram.me/++" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>Pinterest</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $pinterest ?>" name="pinterest" autocomplete="off" placeholder="https://www.pinterest.com/" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>Instagram</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $instagram ?>" name="instagram" autocomplete="off" placeholder="https://instagram.com/" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>Github</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $github ?>" name="github" autocomplete="off" placeholder="https://github.com/" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>Tumblr</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $tumbir ?>" name="tumbir" autocomplete="off" placeholder="https://tumblr.com/" class="form-control form-control-sm focus-ring focus-ring-light" type="text">
                                        </div>
                                    </div>



                                </div>
                                <div class="card-footer">
                                </div>
                            </div>


                        </div>
                        <!-- Social Links -->



                        <!-- Account Settings -->
                        <div class="row tabs d-none" id="div-account">
                            <div class="card" style="border: 0;">
                                <div class="card-header">
                                    <h5>Account Settings</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row" style="padding-top:0">
                                        <div class="col-3 align-items-center d-flex">
                                            <label>Email</label>
                                        </div>
                                        <div class="col">
                                            <input value="<?= $email ?>" name="email" autocomplete="off" placeholder="Email" class="form-control form-control-sm focus-ring focus-ring-light" type="email">
                                        </div>
                                        <div class="email-verification d-none col d-flex align-items-start">
                                            <input style="width:200px" name="email_verification" id="email_verification" autocomplete="off" autofocus placeholder="Verification Code" class="me-2 form-control form-control-sm focus-ring focus-ring-light" type="text">
                                            <span style="padding:0px 12px; line-height:1em; margin:0" class="verification-alert alert alert-warning text-center"><i class="ri-error-warning-fill"></i> Verification code has sent<br>to your email</span>
                                        </div>
                                        <div class="email-invalid d-none col d-flex align-items-center">
                                            <span style="padding:3px 12px; line-height:1em; margin:0" class="alert alert-danger text-center"><i class="ri-error-warning-fill"></i> Invalid Email</span>
                                        </div>
                                        <div class="email-exist d-none col d-flex align-items-center">
                                            <span style="padding:3px 12px; line-height:1em; margin:0" class="alert alert-warning text-center"><i class="ri-error-warning-fill"></i> Email Already Exist!</span>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label class="me-2">Password</label><span id="password-invalid" class="d-none" style="color:darkred; font-weight:1000" id="passnotsame">*PASSWORD INVALID!</span>
                                        </div>
                                        <div class="col">
                                            <input name="password" id="password" placeholder="Password" class="form-control form-control-sm focus-ring focus-ring-light" type="password">
                                            <div class="password-validation d-none" style="font-weight: 1000;">
                                                <b>Password must contain</b><span>: </span>
                                                <span class="validate-8">Atleast 8 characters</span><span>, </span>
                                                <span class="validate-special">1 special character</span><span>, </span>
                                                <span class="validate-number">1 number</span><span>, </span>
                                                <span class="validate-upper">1 uppercase letter</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-3 align-items-center d-flex">
                                            <label class="me-2">Retype password</label><span class="d-none password-re-enter" style="color:darkred; font-weight:1000" id="reenterpass">*RE ENTER PASSWORD!</span><span class="d-none password-not-same" style="color:darkred; font-weight:1000" id="passnotsame">*PASSWORD NOT SAME!</span>
                                        </div>
                                        <div class="col">
                                            <input name="retype_password" id="retype-password" placeholder="Retype password" class="form-control form-control-sm focus-ring focus-ring-light" type="password">
                                            <div class="re-password-validation d-none" style="font-weight: 1000;">
                                                <b>Password must contain</b><span>: </span>
                                                <span class="re-validate-8">Atleast 8 characters</span><span>, </span>
                                                <span class="re-validate-special">1 special character</span><span>, </span>
                                                <span class="re-validate-number">1 number</span><span>, </span>
                                                <span class="re-validate-upper">1 uppercase letter</span>
                                            </div>
                                        </div>
                                    </div>





                                </div>
                                <div class="card-footer">
                                </div>
                            </div>


                        </div>
                        <!-- Account Settings -->



                        <!-- save button -->
                        <div class="save-profile mt-3 mb-5">
                            <button id="saveBtn" type="submit" class="btn btn-primary"><i class="ri-checkbox-circle-fill"></i> Save</button>
                        </div>

                        <!-- Content -->
                    </form>
                    <!-- form -->


                </div>












            </main>

            <div class="overlay"></div>

        </div>

    </div>

    <!-- partial -->









    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header" style="border-radius: 12px;">
                <img style="height:.9rem; border-radius:50%" src="../tickets/images/success.png" alt="success">
                <strong style="font-size:.8rem" class="me-auto">&nbsp;UPDATED SUCCESSFULLY</strong>
                <small>Just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
















    <?php include("../footer.php") ?>

</body>



<script>
    $(document).ready(function() {
        $('#retype-password').on('input', function() {
            var password = $(this).val();
            var isValid = validatePassword(password);

            $('.re-password-validation').removeClass('d-none');
            $('.re-password-validation span').removeClass('invalid-pass');
            $('.re-password-validation span').addClass('valid-pass');
            if (!isValid) {
                if (password.length < 8) {
                    $('.re-password-validation .re-validate-8').addClass('invalid-pass');
                    $('.re-password-validation .re-validate-8').removeClass('valid-pass');
                } else {
                    $('.re-password-validation .re-validate-8').addClass('valid-pass');
                    $('.re-password-validation .re-validate-8').removeClass('invalid-pass');
                }
                if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                    $('.re-password-validation .re-validate-special').addClass('invalid-pass');
                    $('.re-password-validation .re-validate-special').removeClass('valid-pass');
                } else {
                    $('.re-password-validation .re-validate-special').addClass('valid-pass');
                    $('.re-password-validation .re-validate-special').removeClass('invalid-pass');
                }
                if (!/[0-9]/.test(password)) {
                    $('.re-password-validation .re-validate-number').addClass('invalid-pass');
                    $('.re-password-validation .re-validate-number').removeClass('valid-pass');
                } else {
                    $('.re-password-validation .re-validate-number').addClass('valid-pass');
                    $('.re-password-validation .re-validate-number').removeClass('invalid-pass');
                }
                if (!/[A-Z]/.test(password)) {
                    $('.re-password-validation .re-validate-upper').addClass('invalid-pass');
                    $('.re-password-validation .re-validate-upper').removeClass('valid-pass');
                } else {
                    $('.re-password-validation .re-validate-upper').addClass('valid-pass');
                    $('.re-password-validation .re-validate-upper').removeClass('invalid-pass');
                }


            }
            if (isValid) {
                $('.re-password-validation').addClass('d-none');
            }
        });

        function validatePassword(password) {
            // Check if password length is at least 8 characters
            if (password.length < 8) {
                return false;
            }
            // Check if password contains at least 1 special character
            if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                return false;
            }
            // Check if password contains at least 1 number
            if (!/[0-9]/.test(password)) {
                return false;
            }
            // Check if password contains at least 1 uppercase letter
            if (!/[A-Z]/.test(password)) {
                return false;
            }
            return true;
        }
    });
</script>








<script>
    $(document).ready(function() {
        $('#password').on('input', function() {
            var password = $(this).val();
            var isValid = validatePassword(password);

            $('.password-validation').removeClass('d-none');
            $('.password-validation span').removeClass('invalid-pass');
            $('.password-validation span').addClass('valid-pass');
            if (!isValid) {
                if (password.length < 8) {
                    $('.password-validation .validate-8').addClass('invalid-pass');
                    $('.password-validation .validate-8').removeClass('valid-pass');
                } else {
                    $('.password-validation .validate-8').addClass('valid-pass');
                    $('.password-validation .validate-8').removeClass('invalid-pass');
                }
                if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                    $('.password-validation .validate-special').addClass('invalid-pass');
                    $('.password-validation .validate-special').removeClass('valid-pass');
                } else {
                    $('.password-validation .validate-special').addClass('valid-pass');
                    $('.password-validation .validate-special').removeClass('invalid-pass');
                }
                if (!/[0-9]/.test(password)) {
                    $('.password-validation .validate-number').addClass('invalid-pass');
                    $('.password-validation .validate-number').removeClass('valid-pass');
                } else {
                    $('.password-validation .validate-number').addClass('valid-pass');
                    $('.password-validation .validate-number').removeClass('invalid-pass');
                }
                if (!/[A-Z]/.test(password)) {
                    $('.password-validation .validate-upper').addClass('invalid-pass');
                    $('.password-validation .validate-upper').removeClass('valid-pass');
                } else {
                    $('.password-validation .validate-upper').addClass('valid-pass');
                    $('.password-validation .validate-upper').removeClass('invalid-pass');
                }


            }
            if (isValid) {
                $('.password-validation').addClass('d-none');
            }
        });

        function validatePassword(password) {
            // Check if password length is at least 8 characters
            if (password.length < 8) {
                return false;
            }
            // Check if password contains at least 1 special character
            if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                return false;
            }
            // Check if password contains at least 1 number
            if (!/[0-9]/.test(password)) {
                return false;
            }
            // Check if password contains at least 1 uppercase letter
            if (!/[A-Z]/.test(password)) {
                return false;
            }
            return true;
        }
    });
</script>







<!-- ajax update -->

<script>
    $(document).ready(function() {
        $('#update-my-profile').submit(function(event) {
            // Prevent default form submission
            event.preventDefault();

            var btn = $('#saveBtn');
            btn.html('<i class="ri-loader-2-line"></i> Loading').attr('disabled', true);
            setTimeout(function() {
                btn.html('<i class="ri-checkbox-circle-fill"></i> Save').attr('disabled', false);
            }, 5000);

            const toastLiveExample = document.getElementById('liveToast')

            function showToast() {
                const toastBootstrap = new bootstrap.Toast(toastLiveExample)
                toastBootstrap.show()
            }

            // Get form data
            var formData = new FormData(this);
            formData.append("update-my-profile", true);

            // Send Ajax request
            $.ajax({
                url: 'ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {

                    var res = jQuery.parseJSON(response);


                    if (res.status == 100) {

                        $('.email-invalid').addClass('d-none');
                        $('.email-exist').addClass('d-none');
                        $('.password-not-same').addClass('d-none');
                        $('.password-re-enter').addClass('d-none');


                        $('.email-verification').addClass('d-none');

                        $('#email_verification').val('');
                        $('#password').val('');
                        $('#retype-password').val('');

                        setTimeout(showToast, 100);
                        $('.refresh-my-profile').load("my-profile.php .refresh-my-profile", function() {
                            $('#updateProfile').click(function() {
                                $('#profile-change').click();
                            });

                            $('#profile-change').change(function() {
                                var input = this;
                                if (input.files && input.files[0]) {
                                    var reader = new FileReader();
                                    reader.onload = function(e) {
                                        $('#profile-image').attr('src', e.target.result);
                                    }
                                    reader.readAsDataURL(input.files[0]);
                                }
                            });
                        });

                    } else if (res.status == 150) {
                        $('.password-not-same').addClass('d-none');
                        $('.password-re-enter').addClass('d-none');
                        $(".email-invalid").addClass('d-none');
                        $(".email-exist").addClass('d-none');
                        setTimeout(function() {
                            $('.email-verification').removeClass('d-none');
                        }, 100);
                    } else if (res.status == 151) {
                        $('.password-not-same').addClass('d-none');
                        $('.password-re-enter').addClass('d-none');
                        $(".email-invalid").addClass('d-none');
                        $(".email-exist").addClass('d-none');
                        setTimeout(function() {
                            $('.email-invalid').removeClass('d-none');
                        }, 100);
                    } else if (res.status == 152) {

                        $('.password-not-same').addClass('d-none');
                        $('.password-re-enter').addClass('d-none');
                        $('.email-invalid').addClass('d-none');
                        $('.email-exist').addClass('d-none');
                        setTimeout(function() {
                            $('.email-exist').removeClass('d-none');
                        }, 100);
                    } else if (res.status == 153) {

                        $('.password-not-same').addClass('d-none');
                        $('.password-re-enter').addClass('d-none');
                        $('.email-invalid').addClass('d-none');
                        $('.email-exist').addClass('d-none');
                        setTimeout(function() {
                            $('.verification-alert').removeClass('alert-warning');
                            $('.verification-alert').addClass('alert-danger');
                            $('.verification-alert').html('<i class="ri-error-warning-fill"></i> Verification code<br>not match!');
                            setTimeout(function() {
                                $('.verification-alert').addClass('alert-warning');
                                $('.verification-alert').removeClass('alert-danger');
                                $('.verification-alert').html('<i class="ri-error-warning-fill"></i> Verification code has sent<br>to your email');
                            }, 1500);
                        }, 100);
                    } else if (res.status == 102) {
                        $('.password-not-same').addClass('d-none');
                    } else if (res.status == 103) {

                        $('.email-invalid').addClass('d-none');
                        $('.email-exist').addClass('d-none');
                        $('.password-not-same').addClass('d-none');
                        $('.password-re-enter').addClass('d-none');
                        setTimeout(function() {
                            $('.password-re-enter').removeClass('d-none');
                        }, 100);
                    } else if (res.status == 104) {

                        $('.email-invalid').addClass('d-none');
                        $('.email-exist').addClass('d-none');
                        $('.password-not-same').addClass('d-none');
                        $('.password-re-enter').addClass('d-none');
                        setTimeout(function() {
                            $('.password-not-same').removeClass('d-none');
                        }, 100);
                    }
                },
                error: function(xhr, status, error) {
                    // Handle error
                    alert('Error saving ticket: ' + error);
                }
            });
        });
    });
</script>


<!-- ajax update -->



<!-- navigation -->

<script>
    $(document).ready(function() {


        $("#nav-general").click(function() {
            $("#div-company").addClass("d-none");
            $("#div-general").removeClass("d-none");
            $("#div-social").addClass("d-none");
            $("#div-account").addClass("d-none");
            $("#nav-account").removeClass("active");
            $("#nav-social").removeClass("active");
            $("#nav-company").removeClass("active");
            $("#nav-general").addClass("active");
        });

        $("#nav-company").click(function() {
            $("#div-company").removeClass("d-none");
            $("#div-general").addClass("d-none");
            $("#div-social").addClass("d-none");
            $("#div-account").addClass("d-none");
            $("#nav-account").removeClass("active");
            $("#nav-company").addClass("active");
            $("#nav-social").removeClass("active");
            $("#nav-general").removeClass("active");
        });

        $("#nav-social").click(function() {
            $("#div-social").removeClass("d-none");
            $("#div-company").addClass("d-none");
            $("#div-general").addClass("d-none");
            $("#div-account").addClass("d-none");
            $("#nav-account").removeClass("active");
            $("#nav-company").removeClass("active");
            $("#nav-general").removeClass("active");
            $("#nav-social").addClass("active");
        });

        $("#nav-account").click(function() {
            $("#div-account").removeClass("d-none");
            $("#div-social").addClass("d-none");
            $("#div-company").addClass("d-none");
            $("#div-general").addClass("d-none");
            $("#nav-company").removeClass("active");
            $("#nav-general").removeClass("active");
            $("#nav-social").removeClass("active");
            $("#nav-account").addClass("active");
        });



    });
</script>


<!-- profile update -->
<script>
    $(document).ready(function() {
        $('#updateProfile').click(function() {
            $('#profile-change').click();
        });

        $('#profile-change').change(function() {
            var input = this;
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#profile-image').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        });
    });
</script>
<!-- profile update -->


</html>