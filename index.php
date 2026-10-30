<?php
session_start();
require_once __DIR__ . '/includes/role_permissions.php';
if (isset($_GET['verified'])) {
    echo '<div class="alert alert-success text-center">
            <i class="ri-checkbox-circle-fill"></i> Email successfully verified! You can now login.
          </div>';
}
if (isset($_GET['verification_error'])) {
    echo '<div class="alert alert-danger text-center">
            <i class="ri-close-circle-fill"></i> Verification failed: '.htmlspecialchars($_GET['verification_error']).'
          </div>';
}


$return_url = isset($_GET['return_url']) ? $_GET['return_url'] : '';


if (!empty($_SESSION['logged_in'])) {
    $sessionRole = normalizeUserRole((string) ($_SESSION['role'] ?? ''));
    if ($sessionRole !== '') {
        $_SESSION['role'] = $sessionRole;
        if (!empty($return_url)) {
            header("Location: " . urldecode($return_url));
            exit;
        } else {
            header("location:user/dashboard/dashboard.php");
        }
        exit;
    }
}



include("includes/connections.php");


if (isset($_POST['forgot_submit'])) {

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $forgot_username = $_POST['forgot_username'];
        if (!empty($forgot_username)) {
            $query = "select * from users where email = '$forgot_username' limit 1";
            $result = mysqli_query($con, $query);
            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $recover_email = $row['email'];





                //


                $c = 5;
                function getChar($c)
                {
                    $characters = 'abcdefghijklmnopqrstuvwxyz';
                    $randomString = '';

                    for ($i = 0; $i < $c; $i++) {
                        $index = rand(0, strlen($characters) - 1);
                        $randomString .= $characters[$index];
                    }
                    return $randomString;
                }
                $s = 1;
                function getSpec($s)
                {
                    $characters = '$%&*@#?>';
                    $randomString = '';

                    for ($i = 0; $i < $s; $i++) {
                        $index = rand(0, strlen($characters) - 1);
                        $randomString .= $characters[$index];
                    }
                    return $randomString;
                }
                $u = 1;
                function getUpp($u)
                {
                    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $randomString = '';

                    for ($i = 0; $i < $u; $i++) {
                        $index = rand(0, strlen($characters) - 1);
                        $randomString .= $characters[$index];
                    }
                    return $randomString;
                }
                $num = 1;
                function getNumber($num)
                {
                    $characters = '1234567890';
                    $randomString = '';

                    for ($i = 0; $i < $num; $i++) {
                        $index = rand(0, strlen($characters) - 1);
                        $randomString .= $characters[$index];
                    }
                    return $randomString;
                }
                $characters8 = getChar($c);
                $special1 = getSpec($s);
                $upper1 = getUpp($u);
                $number1 = getNumber($num);


                $recover_password = $special1 . "" . $upper1 . "" . $characters8 . "" . $number1;


                $newpassword = password_hash($recover_password, PASSWORD_DEFAULT);

                //



                $sqlf = "UPDATE users SET password='$newpassword' WHERE email = '$forgot_username'";
                $resultf = mysqli_query($con, $sqlf);






                $subject = "Account recovery";
                $message = "Account Information: <br>Email Address: " . $recover_email . "<br>Password: " . $recover_password;
                include("login/send-recovery.php");
                echo '<script>alert("Your account information has sent to your email!");
                document.location.href="index.php";</script>';
            }
        }
    }
}


?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="links/bootstrap-5.3/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="links/bootstrap-5.3/css/bootstrap.min.css">
    <link rel='stylesheet' href='links/font/montserrat.css'>
    <link rel="stylesheet" href="links/login/style.css">
    <link rel='stylesheet' href='links/icon/remixicon.css'>
</head>
<style>
    .invalid:hover,
    .invalid:focus {
        border: solid .5px darkred;
    }

    body {
        background: url('files/Bulsu_front.png');
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-size: cover;
        background-position: center;
        background-blend-mode: overlay;
        background-color: rgba(0, 0, 0, .85);
    }
</style>

<body>
    <!-- Partial -->
    <img src="files/bulsu_logo-D0uvsv43.png" alt="Logo" style="display:block; max-width:120px; margin:0 auto 5px auto;    margin-top: 70px !important;
    margin-bottom: -50px !important;">
    <div class="login-container">
        <!-- login card -->
        <div id="login-card">


            <form id="login-form" class="form-login">
              
  <h4 class="login__title">Login</h4>
                <div class="input-group mb-2">
                    <span class="input-group-text"><i class="ri-user-4-line"></i></span>
                    <input id="login-input-user" autocomplete="off" required name="login_username" class="login__input form-control focus-ring focus-ring-light" type="text" />
                </div>

                <input type="hidden" name="return_url" value="<?= htmlspecialchars($return_url) ?>">


                <label for="login-input-password" class="login__label">
                    Password
                </label>

                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="ri-lock-password-line"></i></span>
                    <input id="login-input-password" name="login_password" required class="login__input form-control focus-ring focus-ring-light" type="password" />


                    <span class="input-group-text" style="cursor: pointer;" onclick="password_show_hide();">
                        <i class="ri-eye-off-line" id="show_eye"></i>
                        <i class="ri-eye-line d-none" id="hide_eye"></i>
                    </span>

                </div>


                <div id="invalid" class="d-none">
                    <span style="margin-top: .5em; color:orange;" class="login__label">invalid username or password</span>
                </div>
                <div id="login_button">
                    <input type="submit" class="login__submit" value="login">
                </div>
                <div id="login_loading" class="d-none">
                    <button disabled type="submit" class="login__loading">
                        <li class="fa-solid fa-circle-notch fa-spin"></li>
                    </button>
                </div>
                <div id="login_success" class="d-none">
                    <button disabled type="submit" class="login__success">
                        <li class="fa-regular fa-circle-check"></li>&nbsp;login successfully
                    </button>
                </div>
            </form>
            <a href="#" id="forgot_password" class="login__forgot">Forgot Password?</a>
            <a href="#" id="sign_up" class="login__forgot">Sign Up</a>

        </div>
        <!-- login card -->

        <!-- forgot card -->
        <div id="forgot-card" class="d-none">

            <h4 class="forgot__title">Recover Account</h4>
            <h4 class="forgot__title" style="color:#fff;opacity:.8; font-size:10px; margin:0px 0px 12px 0px; font-weight:100">RESET PASSWORD</h4>
            <form id="forgot-form" method="POST" class="form-login">
                <label for="login-input-user" class="login__label">
                    email
                </label>
                <input id="login-input-user" autocomplete="off" required name="forgot_username" class="login__input" type="text" />

                <div id="invalid" class="d-none">
                    <span style="margin-top: .5em; color:orange;" class="login__label">invalid email or password</span>
                </div>
                <div id="login_button">
                    <input type="submit" class="login__submit" name="forgot_submit" value="email account recovery">
                </div>
                <div class="btn_cancel">
                    <button id="forgot_cancel" class="forgot__cancel">Cancel</button>
                </div>
                <div id="login_loading" class="d-none">
                    <button disabled type="submit" class="login__loading">
                        <li class="fa-solid fa-circle-notch fa-spin"></li>
                    </button>
                </div>
                <div id="login_success" class="d-none">
                    <button disabled type="submit" class="login__success">
                        <li class="fa-regular fa-circle-check"></li>&nbsp;login successfully
                    </button>
                </div>
            </form>
        </div>
        <!-- forgot card -->

        <!-- Sign up Modal -->

        <!-- signup card (hidden) -->
        <div id="signup-card" class="d-none">
            <h4 class="login__title">Sign Up</h4>
            <p class="text-center text-muted mb-3">Check your account</p>

            <form id="signup-form" class="form-login">
                <!-- Email -->
                <label for="signup-email" class="login__label">Email</label>
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="ri-mail-line"></i></span>
                    <input id="signup-email" class="login__input form-control" type="email" required>
                </div>
                <!-- First Name -->
                <label for="signup-first_name" class="login__label">FirstName</label>
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="ri-user-line"></i></span>
                    <input id="signup-first_name" class="login__input form-control" type="text" required>
                </div>
                <!-- Last Name -->
                <label for="signup-last_name" class="login__label">LastName</label>
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="ri-user-line"></i></span>
                    <input id="signup-lastname" class="login__input form-control" type="text" required>
                </div>

                <!-- Clientele -->
                <label for="signup-clientele" class="login__label" style="color: #ffffff; margin-bottom: 8px;">Clientele</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" style="background-color: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.3);">
                        <i class="ri-community-line" style="color: #ffffff;"></i>
                    </span>
                    <select id="signup-clientele" class="form-select" style="
        background-color: rgba(255,255,255,0.1);
        color: #ffffff;
        border-color: rgba(255,255,255,0.3);
        padding: 10px;
    ">
                        <option value="" selected disabled style="color: #888888;">Select clientele type</option>
                        <option value="OP" style="color: #ffffff; background-color: #333333;">OP (Office of the President)</option>
                        <option value="OVP" style="color: #ffffff; background-color: #333333;">OVP (Office of the Vice President)</option>
                        <option value="Admin" style="color: #ffffff; background-color: #333333;">Admin</option>
                        <option value="Student" style="color: #ffffff; background-color: #333333;">Student</option>
                    </select>
                </div>

                  <!-- Password -->
        <label for="signup-password" class="login__label">Password</label>
        <div class="input-group mb-3">
            <span class="input-group-text"><i class="ri-lock-line"></i></span>
            <input id="signup-password" class="login__input form-control" type="password" required>
            <span class="input-group-text" style="cursor: pointer;" onclick="password_show_hide('signup-password')">
                <i class="ri-eye-off-line"></i>
            </span>
        </div>

                <!-- Password Requirements -->
                <div class="mb-3" style="background: rgba(255,255,255,0.1); padding: 10px; border-radius: 5px;">
                    <p class="login__label" style="margin-bottom: 5px;">Password must contain:</p>
                    <ul style="color: #ddd; padding-left: 20px; margin: 0; font-size: 0.9em;">
                        <li>Minimum 8 characters</li>
                        <li>At least one uppercase letter</li>
                        <li>At least one number</li>
                        <li>At least one special character</li>
                    </ul>
                </div>

                <div id="signup_button">
                    <input type="submit" class="login__submit" value="Create Account">
                </div>
                <div id="signup_loading" class="d-none">
                    <button disabled type="submit" class="login__loading">
                        <li class="fa-solid fa-circle-notch fa-spin"></li>
                    </button>
                </div>
                <div id="signup_success" class="d-none">
                    <button disabled type="submit" class="login__success">
                        <li class="fa-regular fa-circle-check"></li>&nbsp;Registration successful
                    </button>
                </div>


                <div class="btn_cancel">
                    <button id="signup_cancel" class="forgot__cancel">Back to Login</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Verification Modal -->
<div class="modal fade" id="verificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-0">
                <h5 class="modal-title">Email Verification</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (isset($_SESSION['verification_success'])): ?>
                    <div class="alert alert-success">
                        <i class="ri-checkbox-circle-fill"></i> Your email has been verified successfully!
                    </div>
                    <p>You can now log in to your account.</p>
                <?php elseif (isset($_SESSION['verification_error'])): ?>
                    <div class="alert alert-danger">
                        <i class="ri-close-circle-fill"></i> <?= $_SESSION['verification_error'] ?>
                    </div>
                    <p>Please try registering again or contact support.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Show modal if there's a verification message
$(document).ready(function() {
    <?php if (isset($_SESSION['verification_success']) || isset($_SESSION['verification_error'])): ?>
        $('#verificationModal').modal('show');
        <?php 
        // Clear the session variables
        unset($_SESSION['verification_success']);
        unset($_SESSION['verification_error']); 
        ?>
    <?php endif; ?>
});
</script>


    </form>
    <!-- partial -->




    <script src="links/bootstrap-5.3/js/bootstrap.min.js"></script>
    <script src="links/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://kit.fontawesome.com/be0c9622b4.js" crossorigin="anonymous"></script>
</body>


<script>
    function password_show_hide() {
        var x = document.getElementById("login-input-password");
        var show_eye = document.getElementById("show_eye");
        var hide_eye = document.getElementById("hide_eye");
        hide_eye.classList.remove("d-none");
        if (x.type === "password") {
            x.type = "text";
            show_eye.style.display = "none";
            hide_eye.style.display = "block";
        } else {
            x.type = "password";
            show_eye.style.display = "block";
            hide_eye.style.display = "none";
        }
    }
</script>


<!-- Signup -->
<script>
    function password_show_hide(inputId) {
        var x = document.getElementById(inputId);
        var icon = x.nextElementSibling.querySelector('i');
        if (x.type === "password") {
            x.type = "text";
            icon.classList.replace('ri-eye-off-line', 'ri-eye-line');
        } else {
            x.type = "password";
            icon.classList.replace('ri-eye-line', 'ri-eye-off-line');
        }
    }

    // Add signup toggle functionality
    $(document).ready(function() {
        // Show signup form
        $("#sign_up").click(function() {
            $("#login-card").addClass("d-none");
            $("#signup-card").removeClass("d-none");
        });

        // Return to login from signup
        $("#signup_cancel").click(function() {
            $("#signup-card").addClass("d-none");
            $("#login-card").removeClass("d-none");
        });
    });

    // Signup Form Submission Handling
$(document).on('submit', '#signup-form', function(e) {
    e.preventDefault();
    
    // Show loading state
    $('#signup_button').addClass('d-none');
    $('#signup_loading').removeClass('d-none');
    
    // Validate password meets requirements
    const password = $('#signup-password').val();
    if (!/(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}/.test(password)) {
        alert('Password must contain:\n- 8+ characters\n- 1 uppercase letter\n- 1 number\n- 1 special character');
        $('#signup_loading').addClass('d-none');
        $('#signup_button').removeClass('d-none');
        return;
    }

    // Prepare form data
    const formData = {
        email: $('#signup-email').val(),
        password: password,
        first_name: $('#signup-first_name').val(),
        last_name: $('#signup-lastname').val(),
        clientele: $('#signup-clientele').val()
    };

    // Send to server
    $.ajax({
        type: "POST",
        url: "login/signup-process.php", // This should point to your PHP processor
        data: formData,
        success: function(response) {
            if (response.status === 'success') {
                $('#signup_loading').addClass('d-none');
                $('#signup_success').removeClass('d-none');
                setTimeout(() => window.location.href = "user/dashboard/dashboard.php", 1500);
            } else {
                alert(response.message || 'Registration failed');
                $('#signup_loading').addClass('d-none');
                $('#signup_button').removeClass('d-none');
            }
        },
        error: function() {
            alert('Connection error');
            $('#signup_loading').addClass('d-none');
            $('#signup_button').removeClass('d-none');
        }
    });
});
</script>

<script>
    $(document).ready(function() {

        $("#forgot_password").click(function() {
            $("#forgot-card").removeClass("d-none");
            $("#login-card").addClass("d-none");
            $("#login_username").val();
            $("#login_password").val();
        });

        $("#forgot_cancel").click(function() {
            $("#forgot-card").addClass("d-none");
            $("#login-card").removeClass("d-none");
            $("#forgot_username").val();
        });
    });
</script>
<script>
    $(document).on('submit', '#login-form', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        formData.append("login-submit", true);

        $.ajax({
            type: "POST",
            url: "login/ajax.php",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {

                var res = jQuery.parseJSON(response);
                if (res.status == 100) {
                    $('#login-input-user').removeClass('invalid');
                    $('#login-input-password').removeClass('invalid');
                    $('#invalid').addClass('d-none');
                    $('#login_button').addClass('d-none');
                    $('#login_loading').removeClass('d-none');
                    setTimeout(function() {
                        $('#login_loading').addClass('d-none');
                        $('#login_success').removeClass('d-none');
                        setTimeout(function() {
                            location.assign("admin/dashboard/dashboard.php");
                        }, 1000);
                    }, 500);

                } else if (res.status == 99) {
                    $('#login-input-user').removeClass('invalid');
                    $('#login-input-password').removeClass('invalid');
                    $('#invalid').addClass('d-none');
                    $('#login_button').addClass('d-none');
                    $('#login_loading').removeClass('d-none');
                    setTimeout(function() {
                        $('#login_loading').addClass('d-none');
                        $('#login_success').removeClass('d-none');
                        setTimeout(function() {
                            var redirectUrl = res.return_url ? decodeURIComponent(res.return_url) : "user/dashboard/dashboard.php";

                            location.assign(redirectUrl);

                        }, 1000);
                    }, 500);

                } else if (res.status == 101) {
                    $('#login-input-user').removeClass('invalid');
                    $('#login-input-password').removeClass('invalid');
                    $('#login_button').addClass('d-none');
                    $('#login_loading').removeClass('d-none');
                    setTimeout(function() {
                        $('#login-input-user').addClass('invalid');
                        $('#login-input-password').addClass('invalid');
                        $('#login_button').removeClass('d-none');
                        $('#invalid').removeClass('d-none');
                        $('#login_loading').addClass('d-none');
                    }, 500);

                }
            }
        });

    });
</script>
<!-- Footer -->
    <footer style="text-align:center; color:#fff; padding:15px; margin-top:40px; font-size:14px;">
        &copy; <?php echo date("Y"); ?> <a href="https://snlvirtualpartner.com/" target="_blank" style="color:#fff; text-decoration:none;">SNL Virtual Partner</a>. All rights reserved.
    </footer>

    <script src="links/bootstrap-5.3/js/bootstrap.min.js"></script>
    <script src="links/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://kit.fontawesome.com/be0c9622b4.js" crossorigin="anonymous"></script>
    <!-- Your existing JS scripts remain unchanged -->
</body>
</html>
</html>