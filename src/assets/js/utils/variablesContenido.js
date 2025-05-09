// VARIABLES DE FECHA DE RANGO
export function setVariableFechaRango(idInicio, nameInicio, idFin, nameFin) {
    return `
        <div class="d-flex col-sm-6 col-md-3 col-xl-6 mb-2" id="contentFechaRango1">
            <div class="col-sm-6 col-md-3 col-xl-6 mb-2 me-3" >
                <div class="form-group">
                    <label for="${idInicio}">Desde</label>
                    <div class="input-group">
                        <span class="input-group-text span_fecha_filtrar"><i class="icons fa-regular fa-calendars"></i></span>
                        <input type="text" class="form-control fecha_filtrar" id="${idInicio}" name="${nameInicio}" placeholder="Fecha de Ingreso" required>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-md-3 col-xl-6 mb-2 " id="contentFechaRango2">
                <div class="form-group">
                    <label for="${idFin}">Hasta</label>
                    <div class="input-group">
                        <span class="input-group-text span_fecha_fin_filtrar"><i class="icons fa-regular fa-calendars"></i></span>
                        <input type="text" class="form-control fecha_fin_filtrar" id="${idFin}" name="${nameFin}" placeholder="Fecha de Ingreso" required>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// VARIABLE DE EDAD
export function setVariableEdad(id, name) {
    return `
        <div class="col-sm-12 col-md-3 col-xl-3 mb-2" id="contentEdad">
            <div class="form-group">
                <label for="${id}">Edad</label>
                <div class="input-group">
                    <span class="input-group-text span_edad_filtrar"><i class="icons fa-regular fa-users-line"></i></span>
                    <input type="number" class="form-control" id="${id}" name="${name}" placeholder="Edad del personal" required>
                </div>
            </div>
        </div>
    `;
}

// VARIABLE DE SEXO
export function setVariableSexo(id, name) {
    return `
        <div class="col-sm-12 col-md-3 col-xl-3 mb-2" id="contentSexo">
            <div class="form-group">
                <label for="${id}">Tipo de sexo</label>
                <div class="input-group">
                    <span class="input-group-text span_sexo"><i class="icons fa-regular fa-user-group-simple"></i></span>
                    <select class="form-select form-select-md" id="${id}" name="${name}">
                    </select>
                </div>
            </div>
        </div>
    `;
}

// VARIABLE DE DISCAPACIDAD
export function setVariableDiscapacidad(id, name) {
    return `
        <div class="col-sm-12 col-md-3 col-xl-3 mb-2" id="contentDiscapacidad">
            <div class="form-group">
                <label for="${id}">Tipo De Discapacidad</label>
                <div class="input-group">
                    <span class="input-group-text span_tpDiscapacidad2"><i class="icons fa-solid fa-wheelchair-move"></i></span>
                    <select type="text" class="form-control ignore-validation" id="${id}" name="${name}" placeholder="Tipo de Discapacidad ">
                    </select>
                </div>
            </div>
        </div>
    `;
}

// VARIABLE DE NUMERO DE CARNET
export function setVariableCarnetDiscapacidad(id, name) {
    return `
        <div class="col-sm-6 col-md-6 col-xl-6 col-xxl-3 mb-3" id="contenCarnet">
            <div class="form-group" >
                <label for="${id}">Número de Carnet de Discapacidad</label>
                <div class="input-group">
                    <span class="input-group-text span_carnet"><i class="icons fa-regular fa-id-badge"></i></span>
                    <input type="text" class="form-control " id="${id}" name="${name}" placeholder="Cédula de Identidad" required>
                </div>
            </div>
        </div>
    `;
}

// VARIABLE DE ARCHIVO
export function setVariableArchivo(id, name, text, span, content) {
    return `
        <div class="col-sm-12 col-xl-12 col-xxl-6 mb-2" id="${content}">
            <div class="form-group">
                <label for="${id}">${text}</label>
                <div class="input-group">
                    <span class="input-group-text ${span}"><i class="icons fa-regular fa-file-zipper"></i></span>
                    <input type="file" class="form-control" name="${name}" id="${id}" aria-describedby="inputGroupFileAddon04" aria-label="Upload" required>
                </div>
            </div>
        </div>
    `;
}

// VARIABLE DE PARENTESCO   
export function setVariableParentesco(id, name) {
    return `
        <div class="col-sm-12 col-md-3 col-xl-3 mb-2" id="contentParentesco">
            <div class="form-group">
                <label for="${id}">Parentesco</label>
                <div class="input-group">
                    <span class="input-group-text span_parentesco2"><i class="icons fa-regular fa-person-half-dress"></i></span>
                    <select class="form-select form-select-md estado-parentesco" id="${id}" name="${name}" aria-label="Small select example" aria-placeholder="dasdas" required>
                        <option value="">Selecione un parentesco</option>
                    </select>
                </div>
            </div>
        </div>
    `;
}

// VARIABLE DE CARGO
export function setVariableCargo(id, name) {
    return `
        <div class="col-sm-12 col-md-3 col-xl-3 mb-2" id="contentCargo">
            <div class="form-group">
                <label for="${id}">Cargo</label>
                <div class="input-group">
                    <span class="input-group-text span_cargo_filtrar"><i class="icons fa-duotone fa-regular fa-arrows-down-to-people"></i></span>
                    <select class="form-select form-select-md" id="${id}" name="${name}">
                        <option value="">Selecione un cargo</option>
                    </select>
                </div>
            </div>
        </div>
    `;
}

// VARIABLE DE ESTATUS
export function setVariableEstatus(id, name) {
    return `
        <div class="col-sm-12 col-md-3 col-xl-3 mb-2" id="contentEstatus">
            <div class="form-group">
                <label for="${id}">Estatus</label>
                <div class="input-group">
                    <span class="input-group-text span_estatus_filtrar"><i class="icons fa-regular fa-clipboard"></i></span>
                    <select class="form-select form-select-md estado-estatus_filtrar" id="${id}" name="${name}" aria-label="Small select example" aria-placeholder="dasdas" required>
                        <option value="">Selecione un estatus</option>
                    </select>
                </div>
            </div>
        </div>
    `;
}

// VARIABLE DE ESTADO CIVIL
export function setVariableCivil(id, name) {
    return `
        <div class="col-sm-12 col-md-3 col-xl-3 mb-2" id="contentEstadoCivil">
            <div class="form-group">
                <label for="${id}">Estado civil</label>
                <div class="input-group">
                    <span class="input-group-text span_civil_filtrar"><i class="icons fa-regular fa-clipboard"></i></span>
                    <select class="form-select form-select-md estado-civil_filtrar" id="${id}" name="${name}" aria-label="Small select example" aria-placeholder="dasdas" required>
                    </select>
                </div>
            </div>
        </div>
    `;
}

// VARIABLE DE VIVIENDA
export function setVariableVivienda(id, name) {
    return `
        <div class="col-sm-12 col-md-3 col-xl-3 mb-2" id="contentVivienda">
            <div class="form-group">
                <label for="${id}">Vivienda</label>
                <div class="input-group">
                    <span class="input-group-text span_vivienda_filtrar"><i class="icons fa-regular fa-house-building"></i></span>
                    <select class="form-select form-select-md vivienda_filtrar" id="${id}" name="${name}" aria-label="Small select example" aria-placeholder="dasdas" required>
                    </select>
                </div>
            </div>
        </div>
    `;
}

// VARIABLE DE NUMERO DE VIVIENDA
export function setVariableNumVivienda(id, name) {
    return `
        <div class="col-sm-6 col-md-6 mb-2" id="contenNVivienda">
            <div class="form-group">
                <label for="${id}">N.Vivienda</label>
                <div class="input-group">
                    <span class="input-group-text span_numeroVivienda"><i class="icons fa-regular fa-input-numeric"></i></span>
                    <input type="text" 
                    class="form-control" 
                    id="${id}" 
                    name="${name}" 
                    placeholder="Vivienda"
                    pattern="[0-9]*">
                </div>
            </div>
        </div>
    `;
}

// VARIBLE DE CONTENEDOR DE PISO DE DEPARTAMENTO
export function setContenedorPiso(id, name) {
    return `
        <div class="col-sm-6 col-md-6 mb-2" id="contenPiso">
            <div class="form-group">
                <label for="${id}">N.Piso</label>
                <div class="input-group">
                    <span class="input-group-text span_piso"><i class="icons fa-regular fa-input-numeric"></i></span>
                    <input type="number" class="form-control" id="${id}" name="${name}" placeholder="Numero de piso" required>
                </div>
            </div>
        </div>
    `;
}

// VARIABLE DE DOMBRE DE DEPARTAMENTO
export function setContenedorNombreDepa(id, name) {
    return `
        <div class="col-sm-6 col-md-6 mb-2" id="contenNombreDepa">
            <div class="form-group">
                <label  for="${id}">Nombre de la urbanización</label>
                <div class="input-group">
                    <span class="input-group-text span_urbanizacion"><i class="icons fa-regular fa-tree-city"></i></span>
                    <input type="text" class="form-control" id="${id}" name="${name}" placeholder="Nombre de la urbanizacion" required>
                </div>
            </div>
        </div>
    `;
}

// VARAIBLE DE NUMERO DE DEPARTAMENTO
export function setContenedorNumDepa(id, name) {
    return `
        <div class="col-sm-6 col-md-6 mb-2" id="contenNumDepa">
                <div class="form-group">
                    <label for="${id}">Numero del departamento</label>
                    <div class="input-group">
                        <span class="input-group-text span_numeroDepa"><i class="icons fa-regular fa-input-numeric"></i></span>
                        <input type="text" class="form-control" id="${id}" name="${name}" placeholder="Numero del departamento" required>
                    </div>
                </div>
            </div>
    `;
}

// VARIABLE DE NIVEL ACADEMICO 
export function setVariableAcademico(id, name) {
    return `
        <div class="col-sm-12 col-md-3 col-xl-3 mb-2" id="contentNivelAcademico">
            <div class="form-group">
                <label for="${id}">Nivel Academico</label>
                <div class="input-group">
                    <span class="input-group-text span_academico_filtrar"><i class="icons fa-regular fa-user-graduate"></i></span>
                    <select class="form-select form-select-md" id="${id}" name="${name}">
                    </select>
                </div>
            </div>
        </div>
    `;
}

// VARIABLE DE ESTADO
export function setVariableEstado(id, name) {
    return `
        <div class="col-sm-12 col-md-3 col-xl-3 mb-2" id="contentEstado">
            <div class="form-group">
                <label for="${id}">Estado</label>
                <div class="input-group">
                    <span class="input-group-text span_estado_filtrar"><i class="icons fa-regular fa-clipboard"></i></span>
                    <select class="form-select form-select-md estado_filtrar-estado_filtrar" id="${id}" name="${name}" aria-label="Small select example" aria-placeholder="dasdas" required>
                        <option value="">Selecione un estado</option>
                    </select>
                </div>
            </div>
        </div>
    `;
}

// VARIABLE DE MUNICIPIO
export function setVariableMunicipio(id, name) {
    return `
        <div class="col-sm-12 col-md-3 col-xl-3 mb-2" id="contentMunicipio">
            <div class="form-group">
                <label for="${id}">Municipio</label>
                <div class="input-group">
                    <span class="input-group-text span_municipio_filtrar"><i class="icons fa-regular fa-clipboard"></i></span>
                    <select class="form-select form-select-md municipio_filtrar-municipio_filtrar" id="${id}" name="${name}" aria-label="Small select example" aria-placeholder="dasdas" required>
                        <option value="">Seleccione un municipio</option>
                    </select>
                </div>
            </div>
        </div>
    `;
}

// VARIABLE DE PARROQUIA
export function setVariableParroquia(id, name) {
    return `
        <div class="col-sm-12 col-md-3 col-xl-3 mb-2" id="contentParroquia">
            <div class="form-group">
                <label for="${id}">Parroquia</label>
                <div class="input-group">
                    <span class="input-group-text span_parroquia_filtrar"><i class="icons fa-regular fa-clipboard"></i></span>
                    <select class="form-select form-select-md parroquia_filtrar-parroquia_filtrar" id="${id}" name="${name}" aria-label="Small select example" aria-placeholder="dasdas" required>
                        <option value="">Selecione un parroquia</option>
                    </select>
                </div>
            </div>
        </div>
    `;
}

// VARIABLE DE DEPENDENCIA
export function setVariableDependencia(id, name) {
    return `
        <div class="col-sm-12 col-md-3 col-xl-3 mb-2" id="contentDependencia">
            <div class="form-group">
                <label for="${id}">Dependencia</label>
                <div class="input-group">
                    <span class="input-group-text span_dependencia_filtrar"><i class="icons fa-regular fa-clipboard"></i></span>
                    <select class="form-select form-select-md estado-dependencia_filtrar" id="${id}" name="${name}" aria-label="Small select example" aria-placeholder="dasdas" required>
                        <option value="">Selecione una dependencia</option>
                    </select>
                </div>
            </div>
        </div>
    `;
}

// VARIABLE DE DEPARTAMENTO
export function setVariableDepartamento(id, name) {
    return `
        <div class="col-sm-12 col-md-3 col-xl-3 mb-2" id="contentDepartamento">
            <div class="form-group">
                <label for="${id}">Departamento</label>
                <div class="input-group">
                    <span class="input-group-text span_departamento_filtrar"><i class="icons fa-regular fa-clipboard"></i></span>
                    <select class="form-select form-select-md estado-departamento_filtrar" id="${id}" name="${name}" aria-label="Small select example" aria-placeholder="dasdas" required>
                        <option value="">Selecione un departamento</option>
                    </select>
                </div>
            </div>
        </div>
    `;
}

// CEDULA 
export function setVariableCedulaFamiliar(id, name) {
    return `
      <div class="col-sm-12 col-mb-6 col-xl-6" id="contentCedulaFamiliar">
        <div class="form-group mb-3">
          <label for="${id}">Cédula</label>
          <div class="input-group">
            <span class="input-group-text span_cedulaFamiliar"><i class="icons fa-regular fa-address-card"></i></span>
            <input type="text" class="form-control busquedaCedula selectCedula" id="${id}" name="${name}" placeholder="Cédula de Identidad">
          </div>
        </div>
      </div>
    `;
}

// PRIMER NOMBRE 
export function setVariableNombreFamiliar(id, name) {
    return `
      <div class="col-sm-12 col-mb-6 col-xl-6" id="contentNombreFamiliar">
        <div class="form-group mb-3">
          <label for="${id}">Nombre</label>
          <div class="input-group">
            <span class="input-group-text span_nombreFamiliar"><i class="icons fa-regular fa-user"></i></span>
            <input type="text" class="form-control" id="${id}" name="${name}" placeholder="Primer Nombre">
          </div>
        </div>
      </div>
    `;
}

// PRIMER APELLIDO
export function setVariableApellidoFamiliar(id, name) {
    return `
      <div class="col-sm-12 col-mb-6 col-xl-6" id="contentApellidoFamiliar">
        <div class="form-group mb-3">
          <label for="${id}">Apellido</label>
          <div class="input-group">
            <span class="input-group-text span_apellidoFamiliar"><i class="icons fa-regular fa-user"></i></span>
            <input type="text" class="form-control " id="${id}" name="${name}" placeholder="Primer Apellido">
          </div>
        </div>
      </div>
    `;
}

// CARGAR DOCUMENTO DE ESTADO DE DERECHO
export function setVariableDocumentoFamiliar(id, name, span, nombreDoc) {
    return `
      <div class="col-sm-12 col-mb-6 col-xl-6 contentdocumnetoFamiliar" id="contentdocumnetoFamiliar">
        <div class="form-group mb-3">
          <label for="${id}">${nombreDoc}</label>
          <div class="input-group">
            <span class="input-group-text ${span}"><i class="icons fa-regular fa-file-zipper"></i></span>
            <input type="file" name="${name}" class="form-control " id="${id}" aria-describedby="inputGroupFileAddon04" aria-label="Upload">
          </div>
        </div>
      </div>
    `;
}

export function setVariableCheckboxInces(id, labelText, content) {
    return `
      <div class="mt-3 contenbtn" id=${content}>
        <div class="form-check form-check-inline mb-3 p-0" >
          <div class="checkbox-wrapper-7 d-flex align-items-center">
            <input class="form-check-input tgl tgl-ios ignore-validation" id="${id}" type="checkbox" value="" />
            <label class="tgl-btn pdf" for="${id}"></label>
            <span class="ms-2">${labelText}</span>
          </div>
        </div>
      </div>
    `;
}

//variable de contrasena
export function setVariableContrasena(id, name) {
}

// variable de aelrta basica del sistema 
export function setVariableDeAlertaBasica(texto) {
    return `
    <div class="d-flex alert alert-warning alert-dismissible m-0 contentAlerta mt-2 mb-2" role="alert">
            <div class="d-flex align-items-center alert-icon me-3">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="alert-text">
            ${texto}
            </div>
    </div>
`
}