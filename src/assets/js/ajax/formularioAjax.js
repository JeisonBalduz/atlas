export async function enviarFormulario(url, datos, callbackExito, ejecutarCallback = false, metodo = 'POST' ) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: url,
            type: metodo,
            data: datos,
            processData: false,
            contentType: false,
            cache: false,
            beforeSend: function (){
                cargando.style.display = 'flex';     
            },
            success: function(data, status, response) {
                try {
                    // Intenta parsear los datos como JSON
                    const parsedData = data;
                     console.table(parsedData)

                    if (ejecutarCallback) {
                        callbackExito(parsedData);
                    }
                    cargando.style.display = 'none';                
                } catch (error) {
                    console.error('Error al parsear los datos JSON:', error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log (jqXHR, textStatus, errorThrown);
            }
        });
    });
}

export async function enviarDatos(url, datos, metodo = 'POST' ) {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: url,
            type: metodo,
            data: datos,
            processData: false,
            contentType: false,
            cache: false,
            success: function(data, status, response) {
                const parsedData2 = data;
                console.log("Enviado");
                console.table(parsedData2)
                cargando.style.display = 'none';  
                if(data.url){
                    window.location.href = data.url;
                }else{
                console.log("no se puede redireccionar");
                }              
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log (jqXHR, textStatus, errorThrown);
            }
        });
    });
}

export function enviarDatosPersonalizados(url, datos, metodo = 'POST') {
    $.ajax({
        url: url,
        type: metodo,
        data: datos,
        processData: false,
        contentType: false,
        cache: false,
        dataType: 'json',
        success: function (data, status, response) {
            const parsedData2 = data;
            console.log("Enviado");
            console.table(parsedData2)
            cargando.style.display = 'none';
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR, textStatus, errorThrown);
        }
    });
}
export async function obtenerDatos(url, metodo = 'POST') {
  return new Promise((resolve, reject) => {
    $.ajax({
      url,
      type: metodo,
      success: (data) => {
        // console.log("Datos obtenidos:", data);
        resolve({
            exito: data.exito,
            response: data
        }); // Resuelve la promesa con los datos
      },
      error: (jqXHR, textStatus, errorThrown) => {
        console.error('Error al obtener los datos:', errorThrown);
        reject(errorThrown);
      }
    });
  });
}
// Función para generar un hash seguro con sal
export function generarHashContrasena(contrasena) {
    // Generar una sal aleatoria de 16 bytes (32 caracteres hexadecimales)
    const salt = CryptoJS.lib.WordArray.random(16);
    const hash = CryptoJS.SHA256(contrasena + salt);
    return hash.toString() + salt.toString();
  }
  
// Función para verificar una contraseña
export function verificarContrasena(contrasena, hashAlmacenado) {
    // Separar el hash y la sal del string almacenado
    const hashParte = hashAlmacenado.substring(0, 64); // Hash SHA-256 tiene 64 caracteres hexadecimales
    const saltParte = hashAlmacenado.substring(64);
    // Reconstruir el hash original con la sal
    const hashCalculado = CryptoJS.SHA256(contrasena + saltParte);
    // Comparar los hashes
    return hashCalculado.toString() === hashParte;
}
