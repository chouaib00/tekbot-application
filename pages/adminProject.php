<!DOCTYPE html>
<html lang="en">
<head>


	<?php include_once('../includes/header.php'); ?>

	<!-- Custom styles for this template-->
  <link href="../assets/css/sb-admin.css" rel="stylesheet">
	<title>Admin Control Users</title>
</head>

<?php require_once('../db/dbManager.php'); ?>
<?php require_once('../modules/createCards.php'); ?>
<?php //require_once('../modules/redirect.php'); ?>

<?php
if($_SESSION['accessLevel'] != 'Admin'){
	echo('<script type="text/javascript">alert("You are not authorized to be here!")</script>');
	header("Location: ./index.php"); /* Redirect Browser */
}
?>

<body style="background-color:silver">
	<?php include_once("../modules/navbar.php"); ?>
	<br><br>
	<div class="container-fluid">

		<h1>Admin Project Approval</h1>
		<div class="row">

			<div class="col-sm-3">
				<h2>Search and Filter</h2>
				<div class="row">
					<div class="col-sm-12">
						<input class="form-control" id="filterInput" type="text" placeholder="Search...">
						<br>
						<button type="button" style="float:right;" class="btn btn-outline-secondary">Search</button>
						<br><br>

						<div class="form-check">
							<input type="checkbox" class="form-check-input" id="ApprovalRequiredCheckBox">
							<label for="ApprovalRequiredCheckBox">Hide projects do NOT need Admin Approval</label>
						</div>

						<div class="form-check">
							<input type="checkbox" class="form-check-input" id="NDAFilterCheckBox">
							<label for="NDAFilterCheckBox">Hide projects that require an NDA/IP</label>
						</div>

						<div class="form-group">
							<label for="projectTypeFilterSelect">Filter by Keyword</label>
							<select class="form-control" id="keywordFilterSelect" onchange="filterSelectChanged(this)">
								<option></option>
								<?php
									$result = getKeywords();
									while($row = $result->fetch_assoc()){
										echo '<option>' . $row['name'] . '</option>';
									}
								?>
							</select>
						</div>
					</div>

					<div class="col-sm-6">
						<div class="form-group">
							<label for="projectTypeFilterSelect">Filter by Project Type</label>
							<select class="form-control" id="projectTypeFilterSelect" onchange="filterSelectChanged(this)">
								<option></option>
								<option>Capstone</option>
								<option>Internship</option>
								<option>Long Term</option>
								<option>Other</option>
							</select>
						</div>
						<div class="form-group">
							<label for="yearFilterSelect">Filter by Year</label>
							<select class="form-control" id="yearFilterSelect" onchange="filterSelectChanged(this)">
								<option></option>
								<option><?php echo date("Y"); ?></option>
								<option><?php echo date("Y") - 1; ?></option>
								<option><?php echo date("Y") - 2; ?></option>
								<option><?php echo date("Y") - 3; ?></option>
								<option><?php echo date("Y") - 4; ?></option>
								<option><?php echo date("Y") - 5; echo " and earlier"; ?></option>
							</select>
						</div>
					</div>

					<div class="col-sm-6">
						Sort By...
						<div class="custom-control custom-radio">
						  <input type="radio" id="sortTitleAscRadio" value="sortTitleAsc" name="sortRadio" class="custom-control-input">
						  <label class="custom-control-label" for="sortTitleAscRadio">Title ASC (A..Z)</label>
						</div>
						<div class="custom-control custom-radio">
						  <input type="radio" id="sortTitleDescRadio" value="sortTitleDesc" name="sortRadio" class="custom-control-input">
						  <label class="custom-control-label" for="sortTitleDescRadio">Title DESC (Z..A)</label>
						</div>
						<div class="custom-control custom-radio">
						  <input type="radio" id="sortDateDescRadio" value="sortDateDesc" name="sortRadio" class="custom-control-input">
						  <label class="custom-control-label" for="sortDateDescRadio">Date (Recent)</label>
						</div>
						<div class="custom-control custom-radio">
						  <input type="radio" id="sortDateAscRadio" value="sortDateAsc" name="sortRadio" class="custom-control-input">
						  <label class="custom-control-label" for="sortDateAscRadio">Date (Oldest)</label>
						</div>
					</div>
						<button class="btn btn-lg btn-outline-danger capstone-nav-btn" type="button" data-toggle="modal" id="toggleDeleteProjectBtn">Toggle Delete Project Button</button>
						<div id="deleteText" class="adminText" style="color: red;">Project Deleted: </div>
				</div>
			</div>

			<div class="col-sm-9 scroll jumbotron capstoneJumbotron">
				<div class="card-columns capstoneCardColumns" id="projectCardGroup">
					<!-- createCardGroup() is found in ../modules/createCards.php -->
					<?php createCardGroup(false, true); ?>
				</div>
			</div>

		</div>

	</div>
	<?php include_once("../modules/footer.php"); ?>

</body>

<script type="text/javascript">

$('#toggleDeleteProjectBtn').on('click', function(){
	$(".deleteProjectBtn").toggle();

});



/*********************************************************************************
* Function Name: strstr()
* Description: Mimics strstr() php function that searches for the first occurence
* of a string (needle) in another string (haystack).
*********************************************************************************/
function strstr(haystack, needle, bool) {
    var pos = 0;
    haystack += '';
    pos = haystack.toLowerCase().indexOf((needle + '').toLowerCase());
    if (pos == -1) {
        return false;
    } else {
        if (bool) {
            return haystack.substr(0, pos);
        } else {
            return haystack.slice(pos);
        }
    }
}

$(document).ready(function(){

  //As each letter is typed in filterInput, filtering of cards will occur.
  //For drop down lists, like filtering by key word, filterInput is programmically
  //filled and keydown behavior is explicitly called.
  $("#filterInput").keydown(function(){
	var value = $(this).val().toLowerCase();

	for(var i = 0; i < <?php echo $numOfCardsCreated; ?>; i++){
		if($("#projectCard" + i).text().toLowerCase().indexOf(value) > -1){
			$("#projectCard" + i).show();
		}
		else{
			$("#projectCard" + i).hide();
		}
	}
  });

  //Fixme: Future Implementation, allow checkbox to be checked and user to
  //still filter additional options.
  $("#NDAFilterCheckBox").change(function(){
	 if($(this).is(":checked")){
		for(var i = 0; i < <?php echo $numOfCardsCreated; ?>; i++){
			if($("#projectCard" + i).text().toLowerCase().indexOf("nda") > -1){
				$("#projectCard" + i).hide();
			}
		}
	 }
	 else{
		for(var i = 0; i < <?php echo $numOfCardsCreated; ?>; i++){
			$("#projectCard" + i).show();
		}
	 }
  });



	$("#ApprovalRequiredCheckBox").change(function(){
	 if($(this).is(":checked")){
		for(var i = 0; i < <?php echo $numOfCardsCreated; ?>; i++){
			if(($("#projectCard" + i).text().toLowerCase().indexOf("needs") <= -1) && ($("#projectCard" + i).text().toLowerCase().indexOf("awaiting") <= -1)) {
				$("#projectCard" + i).hide();
			}
		}
	 }
	 else{
		for(var i = 0; i < <?php echo $numOfCardsCreated; ?>; i++){
			$("#projectCard" + i).show();
		}
	 }
	});

	$("#CategoryRequiredCheckBox").change(function(){
	 if($(this).is(":checked")){
		for(var i = 0; i < <?php echo $numOfCardsCreated; ?>; i++){
			if(($("#projectCard" + i).text().toLowerCase().indexOf("needs") <= -1)) {
				$("#projectCard" + i).hide();
			}
		}
	 }
	 else{
		for(var i = 0; i < <?php echo $numOfCardsCreated; ?>; i++){
			$("#projectCard" + i).show();
		}
	 }
	});

	$('.form-check-input').on('change', function() {
		$('.form-check-input').not(this).prop('checked', false);

	});


  //Performs sorting functionality based on which radio button is chosen.
	$('input[name="sortRadio"]').change(function() {
		switch ($(this).val()) {
			case "sortTitleAsc":
				var mylist = $('#projectCardGroup');
				var listitems = mylist.children('div').get();
				listitems.sort(function(a, b) {
				   return $(a).text().toUpperCase().localeCompare($(b).text().toUpperCase());
				});

				$.each(listitems, function(index, item) {
				   mylist.append(item);
				});
				break;
			case "sortTitleDesc":
				var mylist = $('#projectCardGroup');
				var listitems = mylist.children('div').get();
				listitems.sort(function(a, b) {
				   return $(b).text().toUpperCase().localeCompare($(a).text().toUpperCase());
				});

				$.each(listitems, function(index, item) {
				   mylist.append(item);
				});
				break;
			case "sortDateAsc":
				var mylist = $('#projectCardGroup');
				var listitems = mylist.children('div').get();
				listitems.sort(function(a, b) {
				   return strstr($(a).text(), "Last Updated:").toUpperCase().localeCompare(strstr($(b).text(), "Last Updated:").toUpperCase());
				});

				$.each(listitems, function(index, item) {
				   mylist.append(item);
				});
				break;
			case "sortDateDesc":
				var mylist = $('#projectCardGroup');
				var listitems = mylist.children('div').get();
				listitems.sort(function(a, b) {
				   return strstr($(b).text(), "Last Updated:").toUpperCase().localeCompare(strstr($(a).text(), "Last Updated:").toUpperCase());
				});

				$.each(listitems, function(index, item) {
				   mylist.append(item);
				});
				break;
		};
	});

// Automatically check the Hide Projects that do NOT need Admin Approval Checkbox and trigger ajax
	$('#ApprovalRequiredCheckBox').prop('checked', true).change();



});



function filterSelectChanged(filterObject){
	var value = filterObject.value;
	$("#filterInput").val(value);

	//Manually trigger keydown to mimic keydown function feature.
	//Attempted to programmically toggleProjectCard, but ran into
	//logical bug 2/26/19.
    var e = jQuery.Event("keydown");
    e.which = 77;
    $("#filterInput").trigger(e);
}

</script>


</html>