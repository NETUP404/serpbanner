<!-- submit-banner-form.php -->

<?php
if ( isset( $_POST['bes_submit'] ) ) {
    bes_handle_banner_submission();
}
?>

<div class="bes-submit-banner-form">
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="banner_url">URL del Banner:</label>
            <input type="text" name="banner_url" required>
        </div>
        
        <div class="form-group">
            <label for="target_url">URL de Destino:</label>
            <input type="url" name="target_url" required>
        </div>
        
        <div class="form-group">
            <input type="submit" name="bes_submit" value="Enviar Banner" class="button">
        </div>
    </form>
</div>

<style>
/* Estilos para el formulario de envío de banners */
.bes-submit-banner-form {
    background-color: #e9f3ff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    margin: 0 auto;
}

.bes-submit-banner-form .form-group {
    margin-bottom: 15px;
}

.bes-submit-banner-form label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
}

.bes-submit-banner-form input[type="text"],
.bes-submit-banner-form input[type="url"] {
    width: calc(100% - 22px);
    padding: 10px;
    border: 1px solid #0056b3;
    border-radius: 5px;
    box-sizing: border-box;
}

.bes-submit-banner-form input[type="submit"] {
    background-color: #0056b3;
    color: #fff;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.bes-submit-banner-form input[type="submit"]:hover {
    background-color: #003d80;
}
</style>