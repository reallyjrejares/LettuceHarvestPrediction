<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<h3 class="mb-4">Lettuce Records</h3>

<div class="table-container">
<div class="table-header mb-3">
<h5>Records</h5>
<input type="text" id="searchInput" class="form-control search-input" placeholder="Search lettuce">
</div>

<table class="table table-hover">
<thead class="table-success">
<tr>
<th>ID</th>
<th>Variety</th>
<th>Date Planted</th>
<th>Predicted Harvest</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>

<tbody id="tableBody">
<?php $plants = $plants ?? []; ?>
<?php foreach ($plants as $plant): ?>
<tr>
<td><?= esc($plant['id']) ?></td>
<td><?= esc($plant['variety']) ?></td>
<td><?= esc($plant['date_planted']) ?></td>
<td class="harvestDate"><?= esc($plant['predicted_harvest']) ?></td>
<td class="statusCell"></td>
<td class="actions-cell">
<div class="table-actions desktop-actions">
<button type="button" class="btn btn-sm btn-link action-icon-btn edit-plant p-0 me-2" data-id="<?= esc($plant['id']) ?>" aria-label="Edit">
<span class="material-icons">edit</span>
</button>
<button type="button" class="btn btn-sm btn-link action-icon-btn delete-plant p-0" data-id="<?= esc($plant['id']) ?>" aria-label="Delete">
<span class="material-icons">delete</span>
</button>
</div>
<div class="dropdown mobile-actions">
<button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle mobile-action-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Plant actions">
<span class="material-icons">more_vert</span>
</button>
<ul class="dropdown-menu dropdown-menu-end">
<li><button type="button" class="dropdown-item edit-plant" data-id="<?= esc($plant['id']) ?>"><span class="material-icons action-icon">edit</span>Edit</button></li>
<li><button type="button" class="dropdown-item text-danger delete-plant" data-id="<?= esc($plant['id']) ?>"><span class="material-icons action-icon">delete</span>Delete</button></li>
</ul>
</div>
</td>
</tr>
<?php endforeach; ?>

</tbody>

</table>

</div>

<div class="fab">+</div>

<!-- ADD PLANT MODAL -->
<div class="modal fade" id="addPlantModal" tabindex="-1" aria-labelledby="addPlantLabel" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="addPlantLabel">Add Plant</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
<form id="addPlantForm" data-endpoint="<?= site_url('plants') ?>">
<div id="addPlantErrors" class="alert alert-danger d-none"></div>
<div class="mb-3">
<label for="plantVariety" class="form-label">Variety</label>
<select class="form-select" id="plantVariety" name="variety" required>
<option value="" selected disabled>Select variety</option>
<option value="Iceberg">Iceberg</option>
<option value="Romaine">Romaine</option>
<option value="Butterhead">Butterhead</option>
</select>
</div>
<div class="mb-3">
<label for="plantDate" class="form-label">Date Planted</label>
<input type="date" class="form-control" id="plantDate" name="date_planted" required>
</div>
<div class="mb-3">
<div class="form-text">Predicted harvest is auto-calculated by variety.</div>
</div>
<div class="d-flex justify-content-end gap-2">
<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="submit" class="btn btn-success">Add Plant</button>
</div>
</form>
</div>
</div>
</div>
</div>

<!-- EDIT PLANT MODAL -->
<div class="modal fade" id="editPlantModal" tabindex="-1" aria-labelledby="editPlantLabel" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title" id="editPlantLabel">Edit Plant</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
<form id="editPlantForm" data-endpoint="<?= site_url('plants') ?>">
<div id="editPlantErrors" class="alert alert-danger d-none"></div>
<input type="hidden" id="editPlantId">
<div class="mb-3">
<label for="editPlantVariety" class="form-label">Variety</label>
<select class="form-select" id="editPlantVariety" name="variety" required>
<option value="" disabled>Select variety</option>
<option value="Iceberg">Iceberg</option>
<option value="Romaine">Romaine</option>
<option value="Butterhead">Butterhead</option>
</select>
</div>
<div class="mb-3">
<label for="editPlantDate" class="form-label">Date Planted</label>
<input type="date" class="form-control" id="editPlantDate" name="date_planted" required>
</div>
<div class="mb-3">
<div class="form-text">Predicted harvest is auto-calculated by variety.</div>
</div>
<div class="d-flex justify-content-end gap-2">
<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="submit" class="btn btn-primary">Save Changes</button>
</div>
</form>
</div>
</div>
</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>

/* SEARCH */

document.getElementById("searchInput").addEventListener("keyup",function(){
let value=this.value.toLowerCase();
document.querySelectorAll("#tableBody tr").forEach(row=>{
row.style.display=row.innerText.toLowerCase().includes(value)?"":"none";
});
});

/* STATUS AUTO */

function updateRecordStatuses(){
let today=new Date();

document.querySelectorAll("#tableBody tr").forEach(row=>{
let harvest=row.querySelector(".harvestDate").innerText;
let harvestDate=new Date(harvest);
let statusCell=row.querySelector(".statusCell");

let diff=(harvestDate-today)/(1000*60*60*24);

if(diff>7){
statusCell.innerHTML='<span class="status growing">Growing</span>';
}else if(diff>=0){
statusCell.innerHTML='<span class="status ready">Ready</span>';
}else{
statusCell.innerHTML='<span class="status harvested">Harvested</span>';
}
});
}

updateRecordStatuses();

/* ADD / EDIT / DELETE */

let addPlantModal=new bootstrap.Modal(document.getElementById("addPlantModal"));
let editPlantModal=new bootstrap.Modal(document.getElementById("editPlantModal"));

document.querySelector(".fab").addEventListener("click",function(){
addPlantModal.show();
});

const addPlantForm=document.getElementById("addPlantForm");
const addPlantErrors=document.getElementById("addPlantErrors");
const editPlantForm=document.getElementById("editPlantForm");
const editPlantErrors=document.getElementById("editPlantErrors");
const editPlantId=document.getElementById("editPlantId");
const editPlantVariety=document.getElementById("editPlantVariety");
const editPlantDate=document.getElementById("editPlantDate");

function showFormErrors(container, errors){
if(!errors){
container.classList.add("d-none");
container.innerHTML="";
return;
}
let messages=[];
if(typeof errors==="string"){
messages=[errors];
}else{
messages=Object.values(errors);
}
container.innerHTML=messages.map(msg=>`<div>${msg}</div>`).join("");
container.classList.remove("d-none");
}

function buildActionCellMarkup(plantId){
let safeId=String(plantId);
return `<td class="actions-cell">
<div class="table-actions desktop-actions">
<button type="button" class="btn btn-sm btn-link action-icon-btn edit-plant p-0 me-2" data-id="${safeId}" aria-label="Edit">
<span class="material-icons">edit</span>
</button>
<button type="button" class="btn btn-sm btn-link action-icon-btn delete-plant p-0" data-id="${safeId}" aria-label="Delete">
<span class="material-icons">delete</span>
</button>
</div>
<div class="dropdown mobile-actions">
<button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle mobile-action-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Plant actions">
<span class="material-icons">more_vert</span>
</button>
<ul class="dropdown-menu dropdown-menu-end">
<li><button type="button" class="dropdown-item edit-plant" data-id="${safeId}"><span class="material-icons action-icon">edit</span>Edit</button></li>
<li><button type="button" class="dropdown-item text-danger delete-plant" data-id="${safeId}"><span class="material-icons action-icon">delete</span>Delete</button></li>
</ul>
</div>
</td>`;
}

addPlantForm.addEventListener("submit",async function(e){
e.preventDefault();

let variety=document.getElementById("plantVariety").value.trim();
let plantDateValue=document.getElementById("plantDate").value;

if(!variety||!plantDateValue){
showFormErrors(addPlantErrors,"Please fill in all required fields.");
return;
}

let endpoint=this.dataset.endpoint;
let formData=new FormData(this);
showFormErrors(addPlantErrors,null);

try{
let response=await fetch(endpoint,{
method:"POST",
body:formData
});

let data=await response.json();

if(!response.ok||!data.ok){
showFormErrors(addPlantErrors, data.errors || data.error || "Failed to save plant.");
return;
}

let plant=data.plant;
let tableBody=document.getElementById("tableBody");

let row=document.createElement("tr");
row.innerHTML=
`<td>${plant.id}</td>
<td>${plant.variety}</td>
<td>${plant.date_planted}</td>
<td class="harvestDate">${plant.predicted_harvest}</td>
<td class="statusCell"></td>
${buildActionCellMarkup(plant.id)}`;

tableBody.prepend(row);

updateRecordStatuses();

this.reset();
addPlantModal.hide();
showFormErrors(addPlantErrors,null);
}catch(err){
showFormErrors(addPlantErrors,"Network error while saving. Please try again.");
}
});

document.getElementById("tableBody").addEventListener("click",function(e){
let editBtn=e.target.closest(".edit-plant");
let deleteBtn=e.target.closest(".delete-plant");

if(editBtn){
let row=editBtn.closest("tr");
editPlantId.value=editBtn.dataset.id;
editPlantVariety.value=row.children[1].innerText.trim();
editPlantDate.value=row.children[2].innerText.trim();
showFormErrors(editPlantErrors,null);
editPlantModal.show();
}

if(deleteBtn){
let id=deleteBtn.dataset.id;
if(!confirm("Delete this plant?")){
return;
}
let endpoint=addPlantForm.dataset.endpoint+`/${id}/delete`;
fetch(endpoint,{ method:"POST" })
.then(res=>res.json())
.then(data=>{
if(!data.ok){
alert("Failed to delete plant.");
return;
}
let row=deleteBtn.closest("tr");
row.remove();
updateRecordStatuses();
})
.catch(()=>alert("Network error while deleting."));
}
});

editPlantForm.addEventListener("submit",async function(e){
e.preventDefault();

let id=editPlantId.value;
let variety=editPlantVariety.value.trim();
let datePlanted=editPlantDate.value;

if(!id||!variety||!datePlanted){
showFormErrors(editPlantErrors,"Please fill in all required fields.");
return;
}

showFormErrors(editPlantErrors,null);

let endpoint=this.dataset.endpoint+`/${id}/update`;
let formData=new FormData(this);

try{
let response=await fetch(endpoint,{
method:"POST",
body:formData
});

let data=await response.json();

if(!response.ok||!data.ok){
showFormErrors(editPlantErrors, data.errors || data.error || "Failed to update plant.");
return;
}

let plant=data.plant;
let row=[...document.querySelectorAll("#tableBody tr")].find(r=>r.firstElementChild.innerText.trim()===String(plant.id));
if(row){
row.children[1].innerText=plant.variety;
row.children[2].innerText=plant.date_planted;
row.querySelector(".harvestDate").innerText=plant.predicted_harvest;
}

updateRecordStatuses();

editPlantModal.hide();
}catch(err){
showFormErrors(editPlantErrors,"Network error while updating. Please try again.");
}
});

</script>
<?= $this->endSection() ?>
