<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Accesso Negato - Tenuta al Morer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet"> 
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/stile.css" media="screen"> 
    <link rel="stylesheet" href="style/mini.css" media="screen and (max-width:940px)">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <header>
        <div class="login-header-logo">
            <a href="index.php"><img src="images/logo.webp" alt="Home - Tenuta al Morer"></a>
        </div>
        <div class="sub-header">
            <nav id="breadcrumb">
                <div class="breadcrumb-container">
                    <span>Ti trovi in: </span>
                    <a href="index.php"><span class="visually-hidden" lang="en">Home</span><i class="fas fa-home home-icon" aria-hidden="true"></i></a> &gt; <span>Accesso Negato</span>
                </div>
            </nav>
        </div>
    </header>

    <main id="content">
        <div class="auth-container" style="text-align: center;">
            <i class="fas fa-user-lock" style="font-size: 4rem; color: #d9534f; margin-bottom: 20px;"></i>
            
            <h1>Accesso Negato</h1>
            <p>Spiacenti, questa è una "Riserva Privata".</p>
            <p>Non disponi delle autorizzazioni necessarie per visualizzare questa pagina.</p>
            
            <div style="margin-top: 2em;">
                <a href="login.php" class="btn-primary">ACCEDI</a>
                <br><br>
                <a href="index.php" class="btn-primary">Torna alla Home</a>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <small>&copy; 2025 Tenuta al Morer</small>
        </div>
    </footer>
</body>
</html>