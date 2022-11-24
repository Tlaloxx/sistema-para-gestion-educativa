<?php
include 'db_connect.php';
$stat = array("Pendiente", "Comenzado", "En progreso", "Pausado", "Fuera de tiempo", "Finalizado");
$qry = $conn->query("SELECT * FROM project_list where id = " . $_GET['id'])->fetch_array();
foreach ($qry as $k => $v) {
	$$k = $v;
}
$tprog = $conn->query("SELECT * FROM task_list where project_id = {$id}")->num_rows;
$cprog = $conn->query("SELECT * FROM task_list where project_id = {$id} and status = 3")->num_rows;
$prog = $tprog > 0 ? ($cprog / $tprog) * 100 : 0;
$prog = $prog > 0 ?  number_format($prog, 2) : $prog;
$prod = $conn->query("SELECT * FROM user_productivity where project_id = {$id}")->num_rows;
if ($status == 0 && strtotime(date('Y-m-d')) >= strtotime($start_date)) :
	if ($prod  > 0  || $cprog > 0)
		$status = 2;
	else
		$status = 1;
elseif ($status == 0 && strtotime(date('Y-m-d')) > strtotime($end_date)) :
	$status = 4;
endif;
$manager = $conn->query("SELECT *,concat(firstname,' ',lastname) as name FROM users where id = $manager_id");
$manager = $manager->num_rows > 0 ? $manager->fetch_array() : array();
?>
<div class="col-lg-12">
	<div class="row">
		<div class="col-md-12">
			<div class="callout callout-info">
				<div class="col-md-12">
					<div class="row">
						<div class="col-sm-6">
							<dl>
								<dt><b class="border-bottom border-primary"> Asignatura</b></dt><br>
								<dd><?php echo ucwords($name) ?></dd><br>
								<dt><b class="border-bottom border-primary"> Descripción</b></dt><br>
								<dd><?php echo html_entity_decode($description) ?></dd>
							</dl>
						</div>
						<div class="col-md-6">
							<dl>
								<dt><b class="border-bottom border-primary">Inicia</b></dt>
								<dd><?php echo date("F d, Y", strtotime($start_date)) ?></dd>
							</dl><br>
							<dl>
								<dt><b class="border-bottom border-primary">Termina</b></dt>
								<dd><?php echo date("F d, Y", strtotime($end_date)) ?></dd>
							</dl><br>
							<dl>
								<dt><b class="border-bottom border-primary">Estatus</b></dt>
								<dd>
									<?php
									if ($stat[$status] == 'Pendiente') {
										echo "<span class='badge badge-secondary'>{$stat[$status]}</span>";
									} elseif ($stat[$status] == 'Comenzado') {
										echo "<span class='badge badge-primary'>{$stat[$status]}</span>";
									} elseif ($stat[$status] == 'En progreso') {
										echo "<span class='badge badge-info'>{$stat[$status]}</span>";
									} elseif ($stat[$status] == 'Pausado') {
										echo "<span class='badge badge-warning'>{$stat[$status]}</span>";
									} elseif ($stat[$status] == 'Fuera de tiempo') {
										echo "<span class='badge badge-danger'>{$stat[$status]}</span>";
									} elseif ($stat[$status] == 'Finalizado') {
										echo "<span class='badge badge-success'>{$stat[$status]}</span>";
									}
									?>
								</dd>
							</dl>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-4">
			<div class="card card-outline card-primary">
				<div class="card-header">
					<span><b>Alumnos</b></span>
					<div class="card-tools">
						<!-- <button class="btn btn-primary bg-gradient-primary btn-sm" type="button" id="manage_team">Manage</button> -->
					</div>
				</div>
				<div class="card-body">
					<ul class="users-list clearfix">
						<?php
						if (!empty($user_ids)) :
							$members = $conn->query("SELECT *,concat(firstname,' ',lastname) as name FROM users where id in ($user_ids) order by concat(firstname,' ',lastname) asc");
							while ($row = $members->fetch_assoc()) :
						?>
								<li>
									<img src="assets/uploads/<?php echo $row['avatar'] ?>" alt="User Image">
									<a class="users-list-name" href="javascript:void(0)"><?php echo ucwords($row['name']) ?></a>
									<!-- <span class="users-list-date">Today</span> -->
								</li>
						<?php
							endwhile;
						endif;
						?>
					</ul>
				</div>
			</div>
		</div>
		<div class="col-md-8">
			<div class="card card-outline card-primary">
				<div class="card-header">
					<span><b>Lista de clases</b></span>
					<?php if ($_SESSION['login_type'] != 3) : ?>
						<div class="card-tools">
							<button class="btn btn-primary bg-gradient-primary btn-sm" type="button" id="new_task"><i class="fa fa-plus"></i> Añadir clase</button>
						</div>
					<?php endif; ?>
				</div>
				<div class="card-body p-0">
					<div class="table-responsive">
						<table class="table table-condensed m-0 table-hover">
							<colgroup>
								<col width="5%">
								<col width="25%">
								<col width="30%">
								<col width="15%">
								<col width="15%">
							</colgroup>
							<thead>
								<th>#</th>
								<th>Clase</th>
								<th>Descripción</th>
								<th>Estatus</th>
								<th>Acción</th>
							</thead>
							<tbody>
								<?php
								$i = 1;
								$tasks = $conn->query("SELECT * FROM task_list where project_id = {$id} order by task asc");
								while ($row = $tasks->fetch_assoc()) :
									$trans = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);
									unset($trans["\""], $trans["<"], $trans[">"], $trans["<h2"]);
									$desc = strtr(html_entity_decode($row['description']), $trans);
									$desc = str_replace(array("<li>", "</li>"), array("", ", "), $desc);
								?>
									<tr>
										<td class="text-center"><?php echo $i++ ?></td>
										<td class=""><b><?php echo ucwords($row['task']) ?></b></td>
										<td class="">
											<p class="truncate"><?php echo strip_tags($desc) ?></p>
										</td>
										<td>
											<?php
											if ($row['status'] == 1) {
												echo "<span class='badge badge-secondary'>Pendiente</span>";
											} elseif ($row['status'] == 2) {
												echo "<span class='badge badge-primary'>En progreso</span>";
											} elseif ($row['status'] == 3) {
												echo "<span class='badge badge-success'>Finalizado</span>";
											}
											?>
										</td>
										<td class="text-center">
											<button type="button" class="btn btn-default btn-sm btn-flat border-info wave-effect text-info dropdown-toggle" data-toggle="dropdown" aria-expanded="true"> Acción</button>
											<div class="dropdown-menu" style="">
												<a class="dropdown-item view_task" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>" data-task="<?php echo $row['task'] ?>">Ver</a>
												<div class="dropdown-divider"></div>
												<?php if ($_SESSION['login_type'] != 3) : ?>
													<a class="dropdown-item edit_task" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>" data-task="<?php echo $row['task'] ?>">Editar</a>
													<div class="dropdown-divider"></div>
													<a class="dropdown-item delete_task" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>">Eliminar -</a> 
												<?php endif; ?>
											</div>
										</td>
									</tr>
								<?php
								endwhile;
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script>
		$('#new_task').click(function() {
			uni_modal("Añadir clase a <?php echo ucwords($name) ?>", "manage_task.php?pid=<?php echo $id ?>", "mid-large")
		})
		$('.edit_task').click(function() {
			uni_modal("Editar clase: " + $(this).attr('data-task'), "manage_task.php?pid=<?php echo $id ?>&id=" + $(this).attr('data-id'), "mid-large")
		})
		$('.view_task').click(function() {
			uni_modal("Task Details", "view_task.php?id=" + $(this).attr('data-id'), "mid-large")
		})
		$('#new_productivity').click(function() {
			uni_modal("<i class='fa fa-plus'></i> New Progress", "manage_progress.php?pid=<?php echo $id ?>", 'large')
		})
		$('.manage_progress').click(function() {
			uni_modal("<i class='fa fa-edit'></i> Edit Progress", "manage_progress.php?pid=<?php echo $id ?>&id=" + $(this).attr('data-id'), 'large')
		})
		$('.delete_progress').click(function() {
			_conf("Are you sure to delete this progress?", "delete_progress", [$(this).attr('data-id')])
		})

		function delete_progress($id) {
			start_load()
			$.ajax({
				url: 'ajax.php?action=delete_progress',
				method: 'POST',
				data: {
					id: $id
				},
				success: function(resp) {
					if (resp == 1) {
						alert_toast("Data successfully deleted", 'success')
						setTimeout(function() {
							location.reload()
						}, 1500)

					}
				}
			})
		}
	</script>

