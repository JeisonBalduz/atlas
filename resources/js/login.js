const error = document.getElementById("login-error");


document.getElementById("btn-ver-clave").addEventListener("click", (e) => {
  console.log("si le diste clik");
  const show = password.type === "password";
  password.type = show ? "text" : "password";
  e.currentTarget.setAttribute(
    "aria-label",
    show ? "Ocultar contraseña" : "Mostrar contraseña",
  );
});
password.addEventListener("input", () => error.classList.add("oculto"));
window.lucide?.createIcons();
