<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/config/helpers.php';
require_once __DIR__ . '/../../app/config/auth.php';
requireRole(['cliente']);

require_once __DIR__ . '/../../app/config/database.php';
$pdo = Database::getConnection();

// Traer datos del usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute([':id' => $_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

// Traer datos de la solicitud anterior (si existe)
$stmt = $pdo->prepare("
    SELECT * FROM solicitudes 
    WHERE usuario_id = :uid 
    ORDER BY fecha_registro DESC 
    LIMIT 1
");
$stmt->execute([':uid' => $_SESSION['usuario_id']]);
$solicitudAnterior = $stmt->fetch();

// Producto preseleccionado si viene en la URL
$productoIdPre = (int)($_GET['producto_id'] ?? 0);

$productos = $pdo->query("SELECT id, marca, modelo, precio FROM productos WHERE estado = 'activo' ORDER BY marca, modelo")->fetchAll();

$pageTitle = 'Solicitar financiamiento';
require_once __DIR__ . '/../../app/views/layouts/cliente_header.php';
?>

<h2 class="h4 mb-4">Solicitar financiamiento</h2>

<div class="row g-4">
  <div class="col-12 col-lg-7">
    <div class="x-card p-4">
      <form id="formFinanciamiento" action="<?= base_url('cliente/solicitud_guardar.php') ?>" method="POST" enctype="multipart/form-data" class="row g-3">
        <input type="hidden" name="tipo_solicitud" value="financiamiento">

        <!-- Moto de interés -->
        <div class="col-12">
          <label class="form-label">Moto de interés *</label>
          <select name="producto_id" id="producto_id" class="form-select" required>
            <option value="">Selecciona una moto...</option>
            <?php foreach ($productos as $p): ?>
              <option value="<?= $p['id'] ?>" 
                      data-precio="<?= $p['precio'] ?>"
                      <?= $productoIdPre === (int)$p['id'] ? 'selected' : '' ?>>
                <?= e($p['marca'] . ' ' . $p['modelo']) ?> — $<?= number_format((float)$p['precio'], 0, ',', '.') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Nombres *</label>
          <input type="text" name="nombre" class="form-control" 
                 value="<?= e($usuario['nombre_completo']) ?>" readonly>
        </div>

        <div class="col-md-6">
          <label class="form-label">Apellidos *</label>
          <input type="text" name="apellidos" class="form-control" 
                 value="<?= e($solicitudAnterior['apellidos'] ?? '') ?>" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Tipo de documento *</label>
          <select name="tipo_doc" class="form-select" required>
            <option value="CC" <?= ($solicitudAnterior['tipo_doc'] ?? 'CC') === 'CC' ? 'selected' : '' ?>>Cédula de Ciudadanía</option>
            <option value="CE" <?= ($solicitudAnterior['tipo_doc'] ?? '') === 'CE' ? 'selected' : '' ?>>Cédula de Extranjería</option>
            <option value="PA" <?= ($solicitudAnterior['tipo_doc'] ?? '') === 'PA' ? 'selected' : '' ?>>Pasaporte</option>
          </select>
        </div>

        <div class="col-md-8">
          <label class="form-label">Número de documento *</label>
          <input type="text" name="cedula" class="form-control" 
                 value="<?= e($solicitudAnterior['cedula'] ?? '') ?>" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Fecha de nacimiento *</label>
          <input type="date" name="fecha_nac" class="form-control" 
                 value="<?= e($solicitudAnterior['fecha_nac'] ?? '') ?>" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Correo *</label>
          <input type="email" name="correo" class="form-control" 
                 value="<?= e($usuario['email']) ?>" readonly>
        </div>

        <div class="col-md-6">
          <label class="form-label">Teléfono *</label>
          <input type="tel" name="telefono" class="form-control" 
                 value="<?= e($usuario['telefono']) ?>" readonly>
        </div>

        <div class="col-md-6">
          <label class="form-label">Departamento *</label>
          <input type="text" name="departamento" class="form-control" 
                 value="<?= e($solicitudAnterior['departamento'] ?? '') ?>" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Ciudad *</label>
          <input type="text" name="ciudad" class="form-control" 
                 value="<?= e($solicitudAnterior['ciudad'] ?? '') ?>" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Dirección *</label>
          <input type="text" name="direccion" class="form-control" 
                 value="<?= e($solicitudAnterior['direccion'] ?? '') ?>" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Cédula (PDF) *</label>
          <input type="file" name="doc_cedula" class="form-control" accept=".pdf,.jpg,.png" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Certificado de ingresos *</label>
          <input type="file" name="doc_ingresos" class="form-control" accept=".pdf,.jpg,.png" required>
        </div>

        <!-- Campos hidden para el simulador -->
        <input type="hidden" name="monto" id="hiddenMonto" value="">
        <input type="hidden" name="plazo" id="hiddenPlazo" value="36">
        <input type="hidden" name="cuota_inicial_pct" id="hiddenCuotaInicial" value="20">
        <input type="hidden" name="cuota_mensual" id="hiddenCuotaMensual" value="">

        <div class="col-12">
          <div class="form-check d-flex align-items-center gap-2" id="terminosWrapper">
            <input class="form-check-input" type="checkbox" id="terminos" name="terminos" required disabled>
            <label class="form-check-label" for="terminos">
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

        <div class="col-12 d-grid">
          <button type="submit" class="btn btn-x-primary btn-lg">
            <i class="bi bi-send me-2"></i> Enviar solicitud
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- SIMULADOR -->
  <div class="col-12 col-lg-5">
    <div class="x-card p-4">
      <h3 class="h5 mb-3">Simulador</h3>
      <div class="mb-3">
        <label class="form-label">Precio de la moto</label>
        <input type="text" id="monto" class="form-control" readonly
               value="<?= $productoIdPre > 0 ? '' : '$ 25.500.000' ?>">
      </div>
      <div class="row g-3">
        <div class="col-6">
          <label class="form-label">Cuota inicial (%)</label>
          <input type="number" id="cuotaInicial" class="form-control" value="20">
        </div>
        <div class="col-6">
          <label class="form-label">Tasa anual (%)</label>
          <input type="number" id="tasa" class="form-control" value="18">
        </div>
        <div class="col-12">
          <label class="form-label">Plazo (meses)</label>
          <input type="number" id="plazo" class="form-control" value="36">
        </div>
        <div class="col-12">
          <div class="x-card p-3" style="background: var(--x-surface-2);">
            <div class="d-flex justify-content-between mb-2">
              <span>Cuota mensual</span>
              <strong id="outCuota" class="text-x-primary">$ 0</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>Intereses</span>
              <strong id="outInteres">$ 0</strong>
            </div>
            <div class="d-flex justify-content-between">
              <span>Total a pagar</span>
              <strong id="outTotal">$ 0</strong>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function parseMoney(raw) {
    return parseInt(String(raw).replace(/[^\d]/g, '')) || 0;
  }

  function calcular() {
    const monto = parseMoney(document.getElementById('monto').value);
    const pIni = Math.min(Math.max(parseFloat(document.getElementById('cuotaInicial').value) || 0, 0), 90) / 100;
    const tasa = Math.max(parseFloat(document.getElementById('tasa').value) || 0, 0) / 100;
    const plazo = parseInt(document.getElementById('plazo').value) || 0;

    const inicial = monto * pIni;
    const financiado = Math.max(monto - inicial, 0);
    const r = tasa / 12;
    let cuota = 0;
    if (r > 0 && plazo > 0 && financiado > 0) {
      cuota = financiado * r / (1 - Math.pow(1 + r, -plazo));
    } else if (plazo > 0) {
      cuota = financiado / plazo;
    }
    const total = (cuota * plazo) + inicial;
    const intereses = Math.max(total - monto, 0);

    document.getElementById('outCuota').textContent = '$ ' + cuota.toLocaleString('es-CO', {maximumFractionDigits: 0});
    document.getElementById('outInteres').textContent = '$ ' + intereses.toLocaleString('es-CO', {maximumFractionDigits: 0});
    document.getElementById('outTotal').textContent = '$ ' + total.toLocaleString('es-CO', {maximumFractionDigits: 0});

    document.getElementById('hiddenMonto').value = monto;
    document.getElementById('hiddenPlazo').value = plazo;
    document.getElementById('hiddenCuotaInicial').value = document.getElementById('cuotaInicial').value;
    document.getElementById('hiddenCuotaMensual').value = Math.round(cuota);
  }

  const selectProducto = document.getElementById('producto_id');
  if (selectProducto) {
    selectProducto.addEventListener('change', function () {
      const precio = this.options[this.selectedIndex].dataset.precio;
      if (precio) {
        document.getElementById('monto').value = '$ ' + parseInt(precio).toLocaleString('es-CO');
        calcular();
      }
    });

    if (selectProducto.value) {
      const opcion = selectProducto.options[selectProducto.selectedIndex];
      const precio = opcion.dataset.precio;
      if (precio) {
        document.getElementById('monto').value = '$ ' + parseInt(precio).toLocaleString('es-CO');
      }
    }
  }

  ['cuotaInicial', 'tasa', 'plazo'].forEach(id => {
    document.getElementById(id).addEventListener('input', calcular);
  });

  calcular();

  // =====================================================
  // TÉRMINOS Y CONDICIONES
  // =====================================================
  const linkTerminos = document.getElementById('linkTerminos');
  const checkTerminos = document.getElementById('terminos');
  const terminosWrapper = document.getElementById('terminosWrapper');
  const formFinanciamiento = document.getElementById('formFinanciamiento');

  if (terminosWrapper && checkTerminos) {
    terminosWrapper.addEventListener('click', function (e) {
      if (checkTerminos.disabled) {
        e.preventDefault();
        e.stopPropagation();
        alert('⚠️ Debes leer los términos y condiciones antes de aceptarlos.\n\nHaz clic en el enlace "Leer términos y condiciones" de abajo.');
        return false;
      }
    });
  }

  if (linkTerminos && checkTerminos) {
    linkTerminos.addEventListener('click', function () {
      setTimeout(function () {
        checkTerminos.disabled = false;
        checkTerminos.focus();

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
  // ENVÍO POR FETCH
  // =====================================================
  if (formFinanciamiento) {
    formFinanciamiento.addEventListener('submit', function (e) {
      e.preventDefault();

      if (!formFinanciamiento.checkValidity()) {
        formFinanciamiento.reportValidity();
        return;
      }

      if (!checkTerminos.checked) {
        alert('⚠️ Debes aceptar los términos y condiciones.');
        return;
      }

      const formData = new FormData(formFinanciamiento);

      fetch(formFinanciamiento.action, {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('✅ ' + (data.message || 'Solicitud recibida.'));
          window.location.href = '<?= base_url('cliente/mis_solicitudes.php') ?>';
        } else {
          alert('❌ Error: ' + (data.message || 'No fue posible enviar la solicitud.'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error al enviar la solicitud. Intenta de nuevo.');
      });
    });
  }
</script>

<?php require_once __DIR__ . '/../../app/views/layouts/cliente_footer.php'; ?>