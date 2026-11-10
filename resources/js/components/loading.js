export const buttonLoading = (button, loading) => {
    if (loading) {
      button.disabled = true;
      button.classList.add("opacity-70", "cursor-not-allowed");
      button.innerHTML = `
                <div style="width: 24px; height: 24px; min-width: 24px; display: flex; align-items: center; justify-content: center;" class="animate-spin">
                    <svg style="width: 18px; height: 18px;" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3.5" stroke-opacity="0.25"></circle>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"></path>
                    </svg>
                </div>
                Verificando...
            `;
    } else {
      button.disabled = false;
      button.classList.remove("opacity-70", "cursor-not-allowed");
      button.innerHTML = `
                Iniciar Sesión
                <i data-lucide="arrow-right" class="h-4 w-4"></i>
            `;
      // Refrescar iconos de Lucide si el botón los regenera
      if (window.lucide) lucide.createIcons();
    }
  }