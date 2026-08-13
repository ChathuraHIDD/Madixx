    </div>
  </div>
</div>
<script>
document.getElementById('adminSidebarToggle')?.addEventListener('click', function () {
  document.getElementById('adminSidebar').classList.toggle('open');
});
document.querySelectorAll('[data-confirm]').forEach(function (el) {
  el.addEventListener('submit', function (e) {
    if (!confirm(el.dataset.confirm)) e.preventDefault();
  });
});
</script>
</body>
</html>
