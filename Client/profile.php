<?php
include "../connection.php";
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include("header.php");

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

// Fetch user data
$userStmt = $conn->prepare("SELECT * FROM user WHERE user_id = ?");
$userStmt->bind_param("s", $user_id);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();

$role = $user['role'];

// Fetch role-specific data
$roleData = null;
$clientStmt = null;
$dietitianStmt = null;
if ($role === "client") {
    $clientStmt = $conn->prepare("SELECT * FROM client WHERE user_id = ?");
    $clientStmt->bind_param("s", $user_id);
    $clientStmt->execute();
    $roleData = $clientStmt->get_result()->fetch_assoc();
} elseif ($role === "dietitian") {
    $dietitianStmt = $conn->prepare("SELECT * FROM dietitian WHERE user_id = ?");
    $dietitianStmt->bind_param("s", $user_id);
    $dietitianStmt->execute();
    $roleData = $dietitianStmt->get_result()->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['fullname']);
    $email     = trim($_POST['email']);
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Update user table
    if (!empty($newPassword)) {
        if ($newPassword !== $confirmPassword) {
            $error = "Passwords do not match.";
        } else {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE user SET full_name=?, email=?, password=? WHERE user_id=?");
            $upd->bind_param("ssss", $full_name, $email, $hashed, $user_id);
            $upd->execute();
        }
    } else {
        $upd = $conn->prepare("UPDATE user SET full_name=?, email=? WHERE user_id=?");
        $upd->bind_param("sss", $full_name, $email, $user_id);
        $upd->execute();
    }

    if (empty($error)) {
        if ($role === "client") {
            $age        = (int)$_POST['age'];
            $height     = (float)$_POST['height'];
            $weight     = (float)$_POST['weight'];
            $activeness = $_POST['activeness'];
            $goal       = $_POST['goal'];
            $gender     = $_POST['gender'];

            // Recalculate BMI, BMR, TDEE
            $bmi = round($weight / (($height / 100) ** 2), 4);
            if ($gender === 'male') {
                $bmr = round(10 * $weight + 6.25 * $height - 5 * $age + 5, 2);
            } else {
                $bmr = round(10 * $weight + 6.25 * $height - 5 * $age - 161, 2);
            }
            $multipliers = ['lessActive' => 1.2, 'moderatelyActive' => 1.55, 'highlyActive' => 1.725];
            $tdee = round($bmr * ($multipliers[$activeness] ?? 1.2), 2);

            $cUpd = $conn->prepare("UPDATE client SET age=?, height=?, weight=?, physical_activeness=?, goal=?, gender=?, bmi=?, bmr=?, tdee=? WHERE user_id=?");
            $cUpd->bind_param("iddsssddds", $age, $height, $weight, $activeness, $goal, $gender, $bmi, $bmr, $tdee, $user_id);
            $cUpd->execute();

            $ageUpd = $conn->prepare("UPDATE user SET age=? WHERE user_id=?");
            $ageUpd->bind_param("is", $age, $user_id);
            $ageUpd->execute();

        } elseif ($role === "dietitian") {
            $age     = (int)$_POST['age'];
            $address = trim($_POST['address']);
            $contact = trim($_POST['contactNumber']);
            $gender  = $_POST['gender'];

            $dUpd = $conn->prepare("UPDATE dietitian SET age=?, gender=?, address=?, contact_number=? WHERE user_id=?");
            $dUpd->bind_param("issss", $age, $gender, $address, $contact, $user_id);
            $dUpd->execute();

            $ageUpd = $conn->prepare("UPDATE user SET age=? WHERE user_id=?");
            $ageUpd->bind_param("is", $age, $user_id);
            $ageUpd->execute();
        }

        if (empty($error)) {
            $success = "Profile updated successfully!";
            // Refresh data
            $userStmt->execute();
            $user = $userStmt->get_result()->fetch_assoc();
            if ($role === "client") {
                $clientStmt->execute();
                $roleData = $clientStmt->get_result()->fetch_assoc();
            } elseif ($role === "dietitian") {
                $dietitianStmt->execute();
                $roleData = $dietitianStmt->get_result()->fetch_assoc();
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
    <title>My Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --bg:        #f5f3ef;
            --surface:   #ffffff;
            --border:    #e2ddd6;
            --accent:    #4a7c59;
            --accent-lt: #eef4f1;
            --accent-dk: #3a6147;
            --text:      #1a1a1a;
            --muted:     #7a7570;
            --danger:    #c0392b;
            --warn-bg:   #fdf3f2;
            --success-bg:#f0f8f2;
            --radius:    12px;
            --shadow:    0 2px 16px rgba(0,0,0,0.07);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .page-wrap {
            max-width: 720px;
            margin: 0 auto;
        }

        /* ── Header ── */
        .page-header {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 36px;
        }

        .avatar {
            width: 64px; height: 64px;
            border-radius: 50%;
            background: var(--accent);
            display: flex; align-items: center; justify-content: center;
            font-family: 'DM Serif Display', serif;
            font-size: 26px;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(74,124,89,0.3);
        }

        .page-header-text h1 {
            font-family: 'DM Serif Display', serif;
            font-size: 28px;
            font-weight: 400;
            color: var(--text);
        }

        .page-header-text p {
            font-size: 13px;
            color: var(--muted);
            margin-top: 3px;
            text-transform: capitalize;
        }

        /* ── Alerts ── */
        .alert {
            padding: 14px 18px;
            border-radius: var(--radius);
            font-size: 14px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: var(--success-bg); color: var(--accent-dk); border: 1px solid #c3dfc9; }
        .alert-error   { background: var(--warn-bg);    color: var(--danger);     border: 1px solid #f0c8c4; }

        /* ── Card ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .card-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-header h2 {
            font-family: 'DM Serif Display', serif;
            font-size: 18px;
            font-weight: 400;
        }

        .card-header .icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: var(--accent-lt);
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
        }

        .card-body { padding: 24px; }

        /* ── Grid ── */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .form-grid .full { grid-column: 1 / -1; }

        @media (max-width: 560px) {
            .form-grid { grid-template-columns: 1fr; }
            .form-grid .full { grid-column: 1; }
        }

        /* ── Field ── */
        .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 7px;
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14.5px;
            color: var(--text);
            background: #fff;
            transition: border-color .2s, box-shadow .2s;
            appearance: none;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(74,124,89,0.12);
        }

        .field textarea { resize: vertical; min-height: 80px; }

        .field small {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            color: var(--danger);
        }

        /* ── Radio group ── */
        .radio-group {
            display: flex;
            gap: 20px;
            padding-top: 4px;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 14.5px;
            color: var(--text);
            text-transform: none;
            letter-spacing: 0;
            font-weight: 400;
            cursor: pointer;
        }

        .radio-group input[type="radio"] {
            width: 16px; height: 16px;
            accent-color: var(--accent);
        }

        /* ── Password toggle ── */
        .pw-wrap { position: relative; }
        .pw-wrap input { padding-right: 42px; }
        .pw-toggle {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer; color: var(--muted);
            font-size: 16px; line-height: 1;
            padding: 0;
        }

        /* ── Stats row (client) ── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 22px;
        }

        .stat-box {
            background: var(--accent-lt);
            border: 1px solid #c8dece;
            border-radius: 10px;
            padding: 14px 16px;
            text-align: center;
        }

        .stat-box .val {
            font-family: 'DM Serif Display', serif;
            font-size: 22px;
            color: var(--accent-dk);
        }

        .stat-box .lbl {
            font-size: 11px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-top: 3px;
        }

        /* ── Submit ── */
        .btn-submit {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--accent);
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: background .2s, transform .1s;
        }

        .btn-submit:hover  { background: var(--accent-dk); }
        .btn-submit:active { transform: scale(0.98); }

        .form-footer {
            display: flex;
            justify-content: flex-end;
            padding-top: 8px;
        }

        /* ── Divider ── */
        .divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 20px 0;
        }

        /* ── Section label ── */
        .section-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--muted);
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
<div class="page-wrap">

    <!-- Header -->
    <div class="page-header">
        <div class="avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></div>
        <div class="page-header-text">
            <h1><?= htmlspecialchars($user['full_name']) ?></h1>
            <p><?= htmlspecialchars($user['role']) ?> &nbsp;·&nbsp; <?= htmlspecialchars($user['email']) ?></p>
        </div>
    </div>

    <!-- Alerts -->
    <?php if ($success): ?>
        <div class="alert alert-success">✓ &nbsp;<?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error">⚠ &nbsp;<?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <!-- ── General Info ── -->
        <div class="card">
            <div class="card-header">
                <div class="icon">👤</div>
                <h2>General Information</h2>
            </div>
            <div class="card-body">
                <div class="form-grid">
                    <div class="field full">
                        <label for="fullname">Full Name</label>
                        <input type="text" id="fullname" name="fullname"
                               value="<?= htmlspecialchars($user['full_name']) ?>" required>
                        <small id="fullnameError"></small>
                    </div>
                    <div class="field">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email"
                               value="<?= htmlspecialchars($user['email']) ?>" required>
                        <small id="emailError"></small>
                    </div>
                    <div class="field">
                        <label>Gender</label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="gender" value="male"
                                    <?= (($roleData['gender'] ?? '') === 'male' ? 'checked' : '') ?>>
                                Male
                            </label>
                            <label>
                                <input type="radio" name="gender" value="female"
                                    <?= (($roleData['gender'] ?? '') === 'female' ? 'checked' : '') ?>>
                                Female
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Change Password ── -->
        <div class="card">
            <div class="card-header">
                <div class="icon">🔒</div>
                <h2>Change Password</h2>
            </div>
            <div class="card-body">
                <p style="font-size:13px;color:var(--muted);margin-bottom:16px;">Leave blank to keep your current password.</p>
                <div class="form-grid">
                    <div class="field">
                        <label for="new_password">New Password</label>
                        <div class="pw-wrap">
                            <input type="password" id="new_password" name="new_password" placeholder="••••••••">
                            <button type="button" class="pw-toggle" data-target="new_password">👁</button>
                        </div>
                        <small id="passwordError"></small>
                    </div>
                    <div class="field">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="pw-wrap">
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••">
                            <button type="button" class="pw-toggle" data-target="confirm_password">👁</button>
                        </div>
                        <small id="confirmError"></small>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($role === 'client' && $roleData): ?>
        <!-- ── Client Info ── -->
        <div class="card">
            <div class="card-header">
                <div class="icon">📊</div>
                <h2>Health Details</h2>
            </div>
            <div class="card-body">
                <!-- Stats -->
                <div class="stats-row">
                    <div class="stat-box">
                        <div class="val"><?= round($roleData['bmi'], 1) ?></div>
                        <div class="lbl">BMI</div>
                    </div>
                    <div class="stat-box">
                        <div class="val"><?= round($roleData['bmr']) ?></div>
                        <div class="lbl">BMR (kcal)</div>
                    </div>
                    <div class="stat-box">
                        <div class="val"><?= round($roleData['tdee']) ?></div>
                        <div class="lbl">TDEE (kcal)</div>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="field">
                        <label for="age">Age</label>
                        <input type="number" id="age" name="age" min="1" max="120"
                               value="<?= $roleData['age'] ?>" required>
                    </div>
                    <div class="field">
                        <label for="height">Height (cm)</label>
                        <input type="number" id="height" name="height" min="50" max="300" step="0.1"
                               value="<?= $roleData['height'] ?>" required>
                    </div>
                    <div class="field">
                        <label for="weight">Weight (kg)</label>
                        <input type="number" id="weight" name="weight" min="10" max="500" step="0.1"
                               value="<?= $roleData['weight'] ?>" required>
                    </div>
                    <div class="field">
                        <label for="activeness">Physical Activeness</label>
                        <select id="activeness" name="activeness" required>
                            <option value="lessActive"       <?= $roleData['physical_activeness']==='lessActive'       ? 'selected':'' ?>>Less Active</option>
                            <option value="moderatelyActive" <?= $roleData['physical_activeness']==='moderatelyActive' ? 'selected':'' ?>>Moderately Active</option>
                            <option value="highlyActive"     <?= $roleData['physical_activeness']==='highlyActive'     ? 'selected':'' ?>>Highly Active</option>
                        </select>
                    </div>
                    <div class="field full">
                        <label for="goal">Goal</label>
                        <select id="goal" name="goal" required>
                            <option value="weight_loss" <?= $roleData['goal']==='weight_loss' ? 'selected':'' ?>>Weight Loss</option>
                            <option value="weight_gain" <?= $roleData['goal']==='weight_gain' ? 'selected':'' ?>>Weight Gain</option>
                            <option value="maintain"    <?= $roleData['goal']==='maintain'    ? 'selected':'' ?>>Maintain Weight</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <?php elseif ($role === 'dietitian' && $roleData): ?>
        <!-- ── Dietitian Info ── -->
        <div class="card">
            <div class="card-header">
                <div class="icon">🩺</div>
                <h2>Professional Details</h2>
            </div>
            <div class="card-body">
                <div class="form-grid">
                    <div class="field">
                        <label for="age">Age</label>
                        <input type="number" id="age" name="age" min="18" max="100"
                               value="<?= $roleData['age'] ?>" required>
                    </div>
                    <div class="field">
                        <label for="contactNumber">Contact Number</label>
                        <input type="text" id="contactNumber" name="contactNumber"
                               value="<?= htmlspecialchars($roleData['contact_number']) ?>" required>
                        <small id="contactNumberError"></small>
                    </div>
                    <div class="field full">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3" required><?= htmlspecialchars($roleData['address']) ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Submit -->
        <div class="form-footer">
            <button type="submit" class="btn-submit">
                <span>💾</span> Save Changes
            </button>
        </div>

    </form>
</div><!-- /page-wrap -->

<script>
    // Password toggle
    $(".pw-toggle").on("click", function () {
        const id = $(this).data("target");
        const inp = $("#" + id);
        const isText = inp.attr("type") === "text";
        inp.attr("type", isText ? "password" : "text");
        $(this).text(isText ? "👁" : "🙈");
    });

    // Fullname validation
    $("#fullname").on("input", function () {
        const v = $(this).val();
        const re = /^[A-Za-z]+(?:[ .'-][A-Za-z]+)*$/;
        if (!v) {
            $("#fullnameError").text("Full name is required.");
        } else if (v.length < 3) {
            $("#fullnameError").text("Too short.");
        } else if (!re.test(v)) {
            $("#fullnameError").text("Only letters are allowed.");
        } else {
            $("#fullnameError").text("");
        }
    });

    // Email validation
    $("#email").on("input", function () {
        const v = $(this).val().toLowerCase();
        $(this).val(v);
        const re = /^[a-zA-Z0-9]+([._%+-][a-zA-Z0-9]+)*@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!v) {
            $("#emailError").text("Email is required.");
        } else if (!re.test(v)) {
            $("#emailError").text("Enter a valid email address.");
        } else {
            $("#emailError").text("");
        }
    });

    // Password validation
    $("#new_password").on("input", function () {
        const v = $(this).val();
        if (!v) { $("#passwordError").text(""); return; }
        const rules = [
            [/.{8,}/, "Minimum 8 characters required"],
            [/[A-Z]/, "At least 1 uppercase letter required"],
            [/[a-z]/, "At least 1 lowercase letter required"],
            [/[0-9]/, "At least 1 number required"],
            [/[!@#$%^&*(),.?":{}|<>]/, "At least 1 special character required"]
        ];
        const fail = rules.find(([rx]) => !rx.test(v));
        $("#passwordError").text(fail ? fail[1] : "");
    });

    // Confirm password
    $("#confirm_password").on("input", function () {
        const match = $(this).val() === $("#new_password").val();
        $("#confirmError").text(match ? "" : "Passwords do not match.");
    });

    // Contact number (dietitian)
    $("#contactNumber").on("input", function () {
        const v = $(this).val();
        if (v && !/^[0-9]{10}$/.test(v)) {
            $("#contactNumberError").text("Must be exactly 10 digits.");
        } else {
            $("#contactNumberError").text("");
        }
    });

    // Prevent submit if there are errors
    $("form").on("submit", function (e) {
        const errors = ["#fullnameError","#emailError","#passwordError","#confirmError","#contactNumberError"];
        const hasError = errors.some(id => $(id).text().trim() !== "");
        if (hasError) {
            e.preventDefault();
            alert("Please fix the highlighted errors before saving.");
        }
    });
</script>
</body>
</html>

