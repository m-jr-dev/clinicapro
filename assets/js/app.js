
const toggle=document.getElementById('toggleTheme');
if(toggle){
 toggle.addEventListener('click',()=>{
  const html=document.documentElement;
  html.setAttribute('data-theme', html.getAttribute('data-theme')==='light'?'dark':'light');
 });
}
