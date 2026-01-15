(function(){
  const btn = document.getElementById("toggleTheme");
  if(!btn) return;

  const root = document.documentElement;

  function ensureIcon(){
    let icon = btn.querySelector("i");
    if(!icon){
      icon = document.createElement("i");
      icon.className = "fa-solid fa-moon";
      btn.appendChild(icon);
    }
    return icon;
  }

  function applyTheme(theme){
    root.setAttribute("data-theme", theme);

    const icon = ensureIcon();

    if(theme === "light"){
      icon.className = "fa-solid fa-sun";
    }else{
      icon.className = "fa-solid fa-moon";
    }

    // salva no banco
    fetch("update_theme.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ theme: theme })
    });
  }

  // Usa o tema que já veio do PHP no <html data-theme="...">
  const initial = root.getAttribute("data-theme") || "dark";

  // Ajusta o ícone conforme o tema atual
  (function(){
    const icon = ensureIcon();
    if(initial === "light"){
      icon.className = "fa-solid fa-sun";
    }else{
      icon.className = "fa-solid fa-moon";
    }
  })();

  btn.addEventListener("click", function(){
    const current = root.getAttribute("data-theme") || "dark";
    const next = current === "dark" ? "light" : "dark";
    applyTheme(next);
  });
})();
