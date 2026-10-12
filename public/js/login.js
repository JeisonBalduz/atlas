
const form = document.getElementById("form-login"),
  username = document.getElementById("input-usuario"),
  password = document.getElementById("input-clave"),
  error = document.getElementById("login-error");
form.addEventListener("submit", (e) => {
  e.preventDefault();
  const profile = AtlasAuth.authenticate(username.value, password.value);
  if (!profile) {
    error.textContent = "Usuario o contraseña incorrectos.";
    error.classList.remove("oculto");
    return;
  }
  try {
    AtlasAuth.login(profile);
    location.href = "inicio.html";
  } catch {
    error.textContent =
      "Permite el almacenamiento de sesión en tu navegador para iniciar sesión.";
    error.classList.remove("oculto");
  }
});
document.querySelectorAll(".cuenta-demo").forEach((b) =>
  b.addEventListener("click", () => {
    username.value = b.dataset.usuario;
    password.value = "123";
    form.requestSubmit();
  }),
);
document.getElementById("btn-ver-clave").addEventListener("click", (e) => {
  console.log("si le diste clik")
  const show = password.type === "password";
  password.type = show ? "text" : "password";
  e.currentTarget.setAttribute(
    "aria-label",
    show ? "Ocultar contraseña" : "Mostrar contraseña",
  );
});
password.addEventListener("input", () => error.classList.add("oculto"));
window.lucide?.createIcons();
