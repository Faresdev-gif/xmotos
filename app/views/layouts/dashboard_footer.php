    </div><!-- /dashboard-content -->

    <footer class="dashboard-footer">
      <p class="mb-0 small text-x-muted">
        © <?= date('Y') ?> XMOTOS Admin · Panel de administración
      </p>
    </footer>

  </main>
</div><!-- /dashboard-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Toggle sidebar en móvil
  const btnToggle = document.getElementById('btnToggleSidebar');
  if (btnToggle) {
    btnToggle.addEventListener('click', function () {
      document.getElementById('dashboardSidebar').classList.toggle('show');
    });
  }
</script>
</body>
</html>