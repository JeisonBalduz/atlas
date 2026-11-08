# Métodos Públicos Estándar para Controladores

### 1. Listado principal
* **Inglés:** `index`
* **Español:** `index` / `listar` / `render`
* **Descripción:** Muestra la vista principal con la tabla de registros (ej. listado de empleados o vistas de PostgreSQL).

### 2. Formulario de creación
* **Inglés:** `create`
* **Español:** `crear` / `nuevo`
* **Descripción:** Muestra la vista con el formulario vacío para registrar un nuevo elemento.

### 3. Guardar datos (POST)
* **Inglés:** `store`
* **Español:** `guardar` / `almacenar`
* **Descripción:** Procesa los datos enviados por el formulario de creación y los inserta en la base de datos.

### 4. Ver un detalle
* **Inglés:** `show`
* **Español:** `mostrar` / `ver`
* **Descripción:** Muestra la información detallada de un solo registro específico (usando su ID o identificador).

### 5. Formulario de edición
* **Inglés:** `edit`
* **Español:** `editar` / `modificar`
* **Descripción:** Muestra la vista con el formulario prellenado con los datos de un registro existente para modificarlo.

### 6. Actualizar datos (PUT/POST)
* **Inglés:** `update`
* **Español:** `actualizar`
* **Descripción:** Procesa los datos modificados del formulario de edición y los guarda en la base de datos.

### 7. Eliminar registro
* **Inglés:** `destroy` / `delete`
* **Español:** `eliminar` / `borrar`
* **Descripción:** Procesa la eliminación lógica o física de un registro del sistema.