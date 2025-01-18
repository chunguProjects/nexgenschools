<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.min.css">
</head>
<body class="bt-light">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow-lg p-4" style="width: 50%; max-width:400%;">
            <div class="text-center mb-4">
                <h1 class="h4">NextGen School</h1>
                <p class="text-muted">Login to your account</p>

            </div>
            <form action="login.php" method="post">
                <div class="mb-3M,J">
                    <label for="username" class="form-label">Username</label>
                    <input 
                        type="text"
                        class="form-control"
                        id="username"
                        placeholder="Enter your username"
                        required
                        >
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password"
                        class="form-control"
                        id="password"
                        placeholder="Enter your password"
                        required
                        >
                </div>
                <button class="btn btn-primary w-100" type="submit">Login</button>
                <div class="mt-3 text-center">
                    <small class="text-muted">Forgot your pasword? <a href="#">Reset here</a></small>
                </div>
            </form>
        </div>
    
    </div>
    <script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.js"></script>
</body>
</html>