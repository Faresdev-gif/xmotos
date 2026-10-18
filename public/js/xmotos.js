/**
 * XMOTOS - JavaScript global
 */

// =====================================================
// CONFIGURACIÓN GLOBAL
// =====================================================
// Detectar la URL base automáticamente
const BASE_URL = (function () {
  const path = window.location.pathname;
  const index = path.indexOf('/public/');
  if (index !== -1) {
    return path.substring(0, index + 8); // incluye '/public/'
  }
  return '/';
})();

console.log('[XMOTOS] xmotos.js cargado. BASE_URL:', BASE_URL);

document.addEventListener('DOMContentLoaded', function () {
  console.log('[XMOTOS] DOM listo');

  // =====================================================
  // BOTÓN DE COMPARTIR UBICACIÓN
  // =====================================================
  const btnCompartir = document.getElementById('btnCompartirUbicacion');

  if (btnCompartir) {
    btnCompartir.addEventListener('click', async function () {
      const ubicacion = {
        title: 'XMOTOS - Nuestra ubicación',
        text: 'Visítanos en XMOTOS: Cra 7 # 2 sur 99 bl 5 ap 603, Parque Campestre, Soacha',
        url: 'https://www.google.com/maps/search/?api=1&query=Cra+7+%23+2+sur+99+bl+5+ap+603,+Parque+Campestre,+Soacha'
      };

      if (navigator.share) {
        try {
          await navigator.share(ubicacion);
        } catch (err) {
          console.log('Compartir cancelado:', err);
        }
      } else {
        try {
          await navigator.clipboard.writeText(ubicacion.url);
          alert('Enlace de ubicación copiado al portapapeles. ¡Compártelo!');
        } catch (err) {
          prompt('Copia este enlace:', ubicacion.url);
        }
      }
    });
  }

  // =====================================================
  // BOTÓN DE FAVORITOS
  // =====================================================
  const botonesFavorito = document.querySelectorAll('.btn-favorito');
  console.log('[XMOTOS] Botones de favorito encontrados:', botonesFavorito.length);

  botonesFavorito.forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();

      const id = this.dataset.producto;
      const self = this;

      console.log('[XMOTOS] Clic en favorito, producto ID:', id);

      fetch(BASE_URL + 'favorito_agregar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ producto_id: id })
      })
      .then(function (r) {
        console.log('[XMOTOS] Respuesta HTTP:', r.status);
        return r.text();
      })
      .then(function (text) {
        console.log('[XMOTOS] Respuesta raw:', text);

        var data;
        try {
          data = JSON.parse(text);
        } catch (err) {
          console.error('[XMOTOS] Respuesta no es JSON:', text);
          alert('Error del servidor. Revisa la consola.');
          return;
        }

        if (data.success) {
          self.classList.add('activo');
          var icon = self.querySelector('i');
          if (icon) {
            icon.classList.remove('bi-heart');
            icon.classList.add('bi-heart-fill');
          }
          console.log('[XMOTOS] Favorito agregado correctamente');
        } else if (data.requiere_login) {
          if (confirm('Debes iniciar sesión para guardar favoritos. ¿Ir al login?')) {
            window.location.href = BASE_URL + 'login.php?rol=cliente';
          }
        } else {
          alert(data.message || 'Error al agregar favorito.');
        }
      })
      .catch(function (err) {
        console.error('[XMOTOS] Error fetch:', err);
        alert('Error al agregar a favoritos.');
      });
    });
  });

  console.log('[XMOTOS] xmotos.js inicializado correctamente');

});