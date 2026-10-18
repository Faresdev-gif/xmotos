<!-- Vista del formulario de registro (usada por AuthController) -->
<section class="x-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-8 col-xl-7">

        <!-- Mensajes de error -->
        <?php if (!empty($errors)): ?>
          <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger mb-3">
              <i class="bi bi-exclamation-triangle me-2"></i> <?= e($error) ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <!-- Mensajes de éxito -->
        <?php if (!empty($success)): ?>
          <div class="alert alert-success mb-3">
            <i class="bi bi-check-circle me-2"></i> <?= e($success) ?>
          </div>
        <?php endif; ?>

        <div class="x-card p-4 p-md-5">
          <form method="POST" action="<?= base_url('registro.php') ?>" id="registerForm" novalidate>

            <!-- Nombre completo -->
            <div class="mb-3">
              <label for="nombre_completo" class="form-label">
                <i class="bi bi-person text-x-primary me-1"></i> Nombre completo
              </label>
              <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" 
                     placeholder="Juan Pérez" required>
            </div>

            <!-- Correo electrónico -->
            <div class="mb-3">
              <label for="email" class="form-label">
                <i class="bi bi-envelope text-x-primary me-1"></i> Correo electrónico
              </label>
              <input type="email" class="form-control" id="email" name="email" 
                     placeholder="ejemplo@correo.com" required>
              <div class="form-text text-x-muted">
                Te enviaremos un código de verificación a este correo.
              </div>
            </div>

            <!-- Teléfono -->
            <div class="mb-3">
              <label for="telefono" class="form-label">
                <i class="bi bi-phone text-x-primary me-1"></i> Teléfono
              </label>
              <input type="tel" class="form-control" id="telefono" name="telefono" 
                     placeholder="3001234567" required>
            </div>

            <!-- Tipo de usuario -->
            <div class="mb-3">
              <label for="rol" class="form-label">
                <i class="bi bi-person-badge text-x-primary me-1"></i> Tipo de usuario
              </label>
              <select class="form-select" id="rol" name="rol" required onchange="confirmarRol(this)">
                <option value="cliente" selected>👤 Cliente</option>
                <option value="vendedor">💼 Vendedor</option>
                <option value="administrador">🔐 Administrador</option>
              </select>
            </div>

            <!-- Advertencia para Vendedor/Administrador -->
            <div id="rolWarning" class="alert alert-warning mb-3" style="display: none;">
              <p class="mb-2">
                <i class="bi bi-shield-exclamation me-1"></i>
                <strong>Verificación de seguridad requerida.</strong>
              </p>
              <label class="d-flex align-items-start gap-2">
                <input type="checkbox" id="confirmRol" name="confirm_rol">
                <span class="small">
                  Confirmo que deseo registrarme como <strong id="rolNombre"></strong> y acepto la verificación de seguridad.
                </span>
              </label>
            </div>

            <!-- Contraseña -->
            <div class="mb-3">
              <label for="password" class="form-label">
                <i class="bi bi-lock text-x-primary me-1"></i> Contraseña
              </label>
              <input type="password" class="form-control" id="password" name="password" 
                     placeholder="Mínimo 8 caracteres" required minlength="8">
              <div class="password-strength mt-2">
                <div class="bar" id="passwordBar"></div>
              </div>
              <div class="form-text text-x-muted" id="passwordHint">
                Mínimo 8 caracteres, incluye mayúsculas, números y símbolos.
              </div>
            </div>

            <!-- Confirmar contraseña -->
            <div class="mb-3">
              <label for="confirm_password" class="form-label">
                <i class="bi bi-check-circle text-x-primary me-1"></i> Confirmar contraseña
              </label>
              <input type="password" class="form-control" id="confirm_password" 
                     name="confirm_password" placeholder="Repite tu contraseña" required>
            </div>

          <!-- Términos -->
<div class="mb-4">
  <div class="form-check d-flex align-items-center gap-2" id="terminosWrapper">
    <input class="form-check-input" type="checkbox" id="terms" name="terms" required disabled>
    <label class="form-check-label small" for="terms" id="termsLabel">
      Acepto los términos y condiciones
    </label>
  </div>
  <a href="<?= base_url('docs/terminos-y-condiciones.pdf') ?>" 
     target="_blank" 
     class="text-x-primary small ms-4 d-inline-block mt-2"
     id="linkTerminos">
    <i class="bi bi-file-earmark-pdf me-1"></i> Leer términos y condiciones (obligatorio)
  </a>
</div>
</div>

            <!-- Botón de registro -->
            <div class="d-grid">
              <button type="submit" class="btn btn-x-primary btn-lg">
                <i class="bi bi-person-plus me-2"></i> Registrarse
              </button>
            </div>

            <!-- Enlace a login -->
            <div class="text-center mt-4">
              <span class="text-x-muted">¿Ya tienes cuenta?</span>
              <a href="<?= base_url('login.php') ?>" class="text-x-primary fw-bold">Inicia sesión aquí</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Estilos específicos del registro -->
<style>
  .password-strength {
    height: 6px;
    background: var(--x-border);
    border-radius: 3px;
    overflow: hidden;
  }
  .password-strength .bar {
    height: 100%;
    width: 0%;
    transition: width 0.3s ease, background 0.3s ease;
    border-radius: 3px;
  }
  .password-strength .bar.weak { background: #FF6B6B; width: 25%; }
  .password-strength .bar.medium { background: #F39C12; width: 50%; }
  .password-strength .bar.strong { background: #B8FF24; width: 75%; }
  .password-strength .bar.very-strong { background: #27AE60; width: 100%; }
</style>

<!-- JavaScript del registro -->
<script>
  const passwordInput = document.getElementById('password');
  const confirmInput = document.getElementById('confirm_password');
  const passwordBar = document.getElementById('passwordBar');
  const passwordHint = document.getElementById('passwordHint');

  passwordInput.addEventListener('input', function () {
    const password = this.value;
    let strength = 0;

    if (password.length === 0) {
      passwordBar.className = 'bar';
      passwordHint.textContent = 'Mínimo 8 caracteres, incluye mayúsculas, números y símbolos.';
      return;
    }

    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;

    if (strength <= 2) {
      passwordBar.className = 'bar weak';
      passwordHint.textContent = '🔴 Débil - Agrega mayúsculas, números y símbolos.';
    } else if (strength <= 3) {
      passwordBar.className = 'bar medium';
      passwordHint.textContent = '🟡 Media - Mejora agregando más variedad.';
    } else if (strength <= 4) {
      passwordBar.className = 'bar strong';
      passwordHint.textContent = '🟢 Fuerte - Buena contraseña.';
    } else {
      passwordBar.className = 'bar very-strong';
      passwordHint.textContent = '💪 Muy Fuerte - Excelente contraseña.';
    }
  });

  confirmInput.addEventListener('input', function () {
    if (this.value && this.value !== passwordInput.value) {
      this.classList.add('is-invalid');
    } else if (this.value) {
      this.classList.remove('is-invalid');
      this.classList.add('is-valid');
    } else {
      this.classList.remove('is-invalid', 'is-valid');
    }
  });

  function confirmarRol(select) {
    const warning = document.getElementById('rolWarning');
    const rolNombre = document.getElementById('rolNombre');
    const confirmCheck = document.getElementById('confirmRol');

    if (select.value === 'vendedor' || select.value === 'administrador') {
      warning.style.display = 'block';
      rolNombre.textContent = select.value === 'vendedor' ? 'Vendedor' : 'Administrador';
    } else {
      warning.style.display = 'none';
      confirmCheck.checked = false;
    }
  }

  document.getElementById('registerForm').addEventListener('submit', function (e) {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    const rol = document.getElementById('rol').value;
    const confirmRol = document.getElementById('confirmRol');

    if (password !== confirm) {
      e.preventDefault();
      alert('❌ Las contraseñas no coinciden.');
      return false;
    }

    if (password.length < 8) {
      e.preventDefault();
      alert('❌ La contraseña debe tener al menos 8 caracteres.');
      return false;
    }

    if ((rol === 'vendedor' || rol === 'administrador') && !confirmRol.checked) {
      e.preventDefault();
      alert('⚠️ Debes confirmar la verificación de seguridad para este rol.');
      return false;
    }
  });
    // =====================================================
  // TÉRMINOS Y CONDICIONES: HABILITAR CHECKBOX AL ABRIR PDF
  // =====================================================
  const linkTerminos = document.getElementById('linkTerminos');
  const checkTerminos = document.getElementById('terms');
  const terminosWrapper = document.getElementById('terminosWrapper');
  const formRegistro = document.getElementById('registerForm');

  // Detectar clic en el wrapper (porque el input disabled no dispara eventos)
  if (terminosWrapper && checkTerminos) {
    terminosWrapper.addEventListener('click', function (e) {
      // Si el checkbox está deshabilitado, mostrar alerta
      if (checkTerminos.disabled) {
        e.preventDefault();
        e.stopPropagation();
        alert('⚠️ Debes leer los términos y condiciones antes de aceptarlos.\n\nHaz clic en el enlace "Leer términos y condiciones" de abajo.');
        return false;
      }
    });
  }

  // Cuando el usuario abre el PDF, habilitar el checkbox
  if (linkTerminos && checkTerminos) {
    linkTerminos.addEventListener('click', function () {
      setTimeout(function () {
        checkTerminos.disabled = false;
        checkTerminos.focus();

        // Mostrar mensaje informativo
        if (!document.getElementById('hintTerminos')) {
          const hint = document.createElement('div');
          hint.className = 'form-text text-x-primary mt-1 ms-4';
          hint.id = 'hintTerminos';
          hint.innerHTML = '<i class="bi bi-check-circle me-1"></i> Ya puedes marcar la casilla para aceptar los términos.';
          linkTerminos.parentNode.appendChild(hint);
        }
      }, 800);
    });
  }

  // =====================================================
  // VALIDACIÓN AL ENVIAR
  // =====================================================
  if (formRegistro) {
    formRegistro.addEventListener('submit', function (e) {
      const password = document.getElementById('password').value;
      const confirm = document.getElementById('confirm_password').value;
      const rol = document.getElementById('rol').value;
      const confirmRol = document.getElementById('confirmRol');

      if (password !== confirm) {
        e.preventDefault();
        alert('❌ Las contraseñas no coinciden.');
        return false;
      }

      if (password.length < 8) {
        e.preventDefault();
        alert('❌ La contraseña debe tener al menos 8 caracteres.');
        return false;
      }

      if ((rol === 'vendedor' || rol === 'administrador') && !confirmRol.checked) {
        e.preventDefault();
        alert('⚠️ Debes confirmar la verificación de seguridad para este rol.');
        return false;
      }

      if (!checkTerminos.checked) {
        e.preventDefault();
        alert('⚠️ Debes aceptar los términos y condiciones.');
        return false;
      }
    });
  }
</script>