<?php

use App\Atlas\config\App;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | ATLAS</title>
    <?php require_once App::URL_INC . "icons.php"; ?>
    <link rel="stylesheet" href="<?php echo App::URL_CSS . "registroPersonal.css"; ?>">
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper conten-main">
        <?php require_once App::URL_INC . "/menu.php"; ?>
        <div class="modal" tabindex="-1" id="modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Datos De Trabajador</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-6 mb-1" style="display: none;">
                                <label class="form-label mb-0" for="primerNombre">id</label>
                                <div class="input-group">
                                    <span class="input-group-text "><i class="fa-regular fa-user"></i></span>
                                    <input type="text" class="form-control" id="id" name="primerNombre" placeholder="Primer Nombre">
                                </div>
                            </div>
                            <div class="col-sm-6 mb-1">
                                <label class="form-label mb-0" for="primerNombre">Primer Nombre</label>
                                <div class="input-group">
                                    <span class="input-group-text "><i class="fa-regular fa-user"></i></span>
                                    <input type="text" class="form-control" id="nombreTrabajador" name="primerNombre" placeholder="Primer Nombre">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
        <main class="app-main p-0">
            <div class="imagen-pages" style="height: 17%;">
                <div class="d-flex align-items-center" style="position: absolute; height: 16%;">
                    <span class="ms-4 fw-bold fs-4 text-white">Registro del Personal</span>
                </div>
                <img src="<?php echo App::URL_IMG . "top-header.png"; ?>" alt="" class="w-100 h-100" style="object-fit: cover; object-position:center;">
            </div>
            <div class="contenForm m-2 h-100 p-2">
                <form action="#" class="contact-form form-validate h-100 d-flex" novalidate="novalidate" id="formulario_registro">
                    <div class="row m-0 w-75 h-100 row2 bg-white content w-65 pt-3">
                        <div class="col-sm-6 mb-1">
                            <label class="form-label mb-0" for="primerNombre">Primer Nombre</label>
                            <div class="input-group">
                                <span class="input-group-text span_nombre"><i class="fa-regular fa-user"></i></span>
                                <input type="text" class="form-control" id="primerNombre" name="primerNombre" placeholder="Primer Nombre">
                            </div>
                        </div>

                        <div class="col-sm-6 ">
                            <div class="form-group">
                                <label for="segundoNombre">Segundo Nombre</label>
                                <div class="input-group ">
                                    <span class="input-group-text span_nombre2"><i class="fa-regular fa-user"></i></span>
                                    <input type="text" class="form-control" id="segundoNombre" name="segundoNombre" placeholder="Segundo Nombre">
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 ">
                            <div class="form-group">
                                <label for="primerApellido">Primer Apellido</label>
                                <div class="input-group">
                                    <span class="input-group-text span_apellido"><i class="fa-regular fa-user"></i></span>
                                    <input type="text" class="form-control" id="primerApellido" name="primerApellido" placeholder="Primer Apellido">
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 ">
                            <div class="form-group">
                                <label for="segundoApellido">Segundo Apellido</label>
                                <div class="input-group">
                                    <span class="input-group-text span_apellido2"><i class="fa-regular fa-user"></i></span>
                                    <input type="text" class="form-control" id="segundoApellido" name="segundoApellido" placeholder="segundo Apellido">
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 ">
                            <div class="form-group">
                                <label for="cedula">Cédula</label>
                                <div class="input-group">
                                    <span class="input-group-text span_cedula"><i class="fa-regular fa-address-card"></i></span>
                                    <input type="text" class="form-control" id="cedula" name="cedula" placeholder="Cédula de Identidad">
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 ">
                            <div class="form-group">
                                <label for="civil">Estado civil</label>
                                <div class="input-group">
                                    <span class="input-group-text span_civil"><i class="fa-regular fa-clipboard"></i></span>
                                    <select class="form-select form-select-md estado-civil" id="civil" name="civil" aria-label="Small select example" aria-placeholder="dasdas">
                                        <option value="">Estado civil</option>
                                        <option value="soltero">Soltero</option>
                                        <option value="casado">Casado</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12 ">
                            <div class="form-group">
                                <label for="correo">Correo</label>
                                <div class="input-group">
                                    <span class="input-group-text span_correo"><i class="fa-regular fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="correo" name="correo" placeholder="Correo Electronico">
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4 ">
                            <div class="form-group">
                                <label class="required-field" for="message">Año</label>
                                <div class="input-group">
                                    <span class="input-group-text span_ano"><i class="fa-regular fa-calendar"></i></i></span>
                                    <select class="form-select form-select-md" name="ano" id="ano"></select>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4 ">
                            <div class="form-group">
                                <label class="required-field" for="message">Mes</label>
                                <div class="input-group">
                                    <span class="input-group-text span_mes"><i class="fa-regular fa-calendar"></i></i></span>
                                    <select class="form-select" id="meses" name="meses" aria-label="Default select example"></select>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4 ">
                            <div class="form-group ">
                                <label class="required-field" for="message">Día</label>
                                <div class="input-group">
                                    <span class="input-group-text span_dia"><i class="fa-regular fa-calendar"></i></i></span>
                                    <select class="form-select w-5" id="dia" name="dia" aria-label="Default select example"></select>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12 mb-2">
                            <button type="submit" id="aceptar" name="submit" class="btn btn-primary">
                                <i class="fa-solid fa-plus me-2"></i>Aceptar</button>
                            <button type="button" id="limpiar" name="submit" class="btn btn-warning" style="color: white;">
                                <i class="fa-solid fa-rotate-right me-2"></i>Limpiar
                            </button>
                        </div>

                    </div>
                    <div class="row2 m-0 w-50 ms-4 bg-white content d-flex justify-content-center align-items-center ">
                        <div class="border border-dark d-flex h-75 w-75" id="img-contener">
                        </div>
                    </div>
                </form>
            </div>
        </main>
        <?php require_once App::URL_INC . "/footer.php"; ?>
    </div>
    <?php require_once App::URL_INC . "/scrips.php"; ?>
    <script src="<?php echo App::URL_SCRIPS . "registroPersonal.js" ?>" type="module"></script>
</body>

</html>