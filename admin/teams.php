<?php

$page_title = "Takım Yönetimi";
$projectRoot = dirname(__DIR__); // C:\xampp\htdocs\projeadi
require_once($projectRoot . '/config.php');
require_once 'admin_header.php';

require_once 'admin_sidebar.php';



$pdo = get_db_connection();

$teams = $pdo->query("SELECT id, team_number, team_id_generated, team_name FROM teams ORDER BY team_number ASC")->fetchAll(PDO::FETCH_ASSOC);

?>



<main class="main-content">

<div class="top-bar"> </div>

<div class="content-area">

<div class="page-header">

<h1>Takım Yönetimi</h1>

</div>



<div class="card mb-8">

<h2 class="text-xl font-bold mb-4">Yeni Takım Ekle</h2>

<form action="team_actions.php" method="POST" class="flex items-end gap-4">

<input type="hidden" name="action" value="add_team">

<div class="flex-grow">

<label for="team_number" class="font-medium">Takım Numarası</label>




<input id="team_number" name="team_number" type="number" required placeholder="Örn: 6228" class="mt-2 w-full px-4 py-2.5 border rounded-lg">
    <?php
    if (isset($_GET['fail'])):
        ?>
        <p style="color: red">#<?=$_GET['number']?> numaralı takım zaten var.</p>
    <?php endif;?>
</div>

<button type="submit" class="btn h-12">

<i data-lucide="user-plus" class="mr-2"></i> Takım Oluştur

</button>

</form>

<?php if(isset($_GET['new_team_info'])):

$info = json_decode(urldecode($_GET['new_team_info']), true);

?>

<div class="alert alert-success mt-6">

<strong>Takım #<?php echo htmlspecialchars($info['number']); ?> başarıyla oluşturuldu!</strong>

<p class="mt-2"><strong>Takım ID:</strong> <code><?php echo htmlspecialchars($info['id']); ?></code></p>

<p><strong>Şifre:</strong> <code><?php echo htmlspecialchars($info['password']); ?></code></p>

<p class="text-sm mt-2">Lütfen bu bilgileri takıma iletin.</p>

</div>

<?php endif; ?>

</div>



<div class="card">

<h2 class="text-xl font-bold mb-4">Kayıtlı Takımlar</h2>

<table>

<thead><tr><th>Takım No</th><th>Takım Adı</th><th>Takım ID</th><th>İşlemler</th></tr></thead>

<tbody>

<?php foreach($teams as $team): ?>

<tr>

<td><strong>#<?php echo htmlspecialchars($team['team_number']); ?></strong></td>

<td><?php echo htmlspecialchars($team['team_name']); ?></td>

<td><code><?php echo htmlspecialchars($team['team_id_generated']); ?></code></td>

<td>

<form action="team_actions.php" method="POST" onsubmit="return confirm('Bu takımı silmek istediğinizden emin misiniz?');">

<input type="hidden" name="action" value="delete_team">

<input type="hidden" name="team_db_id" value="<?php echo $team['id']; ?>">

<button type="submit" class="btn btn-sm btn-danger"><i data-lucide="trash-2" class="w-4 h-4"></i> Sil</button>

</form>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</main>



<?php require_once 'admin_footer.php'; ?>