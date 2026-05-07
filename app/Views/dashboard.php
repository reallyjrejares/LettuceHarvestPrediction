<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$latestEnv = ($plants ?? [])[0] ?? null;
$envLocation = 'Your Farm';
$tempC = $latestEnv['temperature_c'] ?? null;
$humidity = $latestEnv['humidity_pct'] ?? null;
$tds = $latestEnv['tds_ppm'] ?? null;
$ph = $latestEnv['ph_level'] ?? null;
$aiPlantDate = $latestEnv['date_planted'] ?? date('Y-m-d');
$aiVariety = $latestEnv['variety'] ?? 'Romaine';
$aiPlantId = $latestEnv['id'] ?? null;
$mlEndpoint = trim(getenv('ML_PREDICT_URL') ?: '');
if ($mlEndpoint !== '' && !preg_match('#^[a-z][a-z0-9+.-]*://#i', $mlEndpoint) && $mlEndpoint[0] !== '/') {
    $scheme = preg_match('#^(localhost|127\\.0\\.0\\.1)(:|/|$)#i', $mlEndpoint) ? 'http://' : 'https://';
    $mlEndpoint = $scheme . $mlEndpoint;
}
$aiPlantOptions = [];
foreach (($plants ?? []) as $plant) {
    $aiPlantOptions[] = [
        'id' => $plant['id'],
        'variety' => $plant['variety'],
        'date_planted' => $plant['date_planted'],
    ];
}
?>

<h3 class="mb-4">Smart Harvest Lettuce Predictions Dashboard</h3>

<!-- WEATHER CARD -->

<div class="weather-card mb-4" data-weather-endpoint="<?= site_url('weather') ?>">
<div class="weather-top">
<div class="weather-location">
<span class="material-icons">location_on</span>
<span id="weatherLocationText"><?= esc($envLocation) ?></span>
</div>
<div class="weather-current">
<span class="material-icons">cloud</span>
<span class="weather-temp" id="weatherTempText">
<?= $tempC !== null ? esc($tempC) . '°C' : '--' ?>
</span>
</div>
</div>
<div class="weather-condition" id="weatherConditionText"></div>
<div class="weather-divider"></div>
<div class="weather-grid">
<div class="weather-item">
<span class="material-icons">thermostat</span>
<div class="weather-value" id="weatherTempValue">
<?= $tempC !== null ? esc($tempC) . '°C' : '--' ?>
</div>
<div class="weather-label">Temperature</div>
</div>
<div class="weather-item">
<span class="material-icons">water_drop</span>
<div class="weather-value" id="weatherHumidityValue">
<?= $humidity !== null ? esc($humidity) . '%' : '--' ?>
</div>
<div class="weather-label">Humidity</div>
</div>
<div class="weather-item">
<span class="material-icons">science</span>
<div class="weather-value" id="weatherTdsValue">
<?= $tds !== null ? esc($tds) . ' ppm' : '--' ?>
</div>
<div class="weather-label">TDS</div>
</div>
<div class="weather-item">
<span class="material-icons">biotech</span>
<div class="weather-value" id="weatherPhValue">
<?= $ph !== null ? esc($ph) : '--' ?>
</div>
<div class="weather-label">pH Level</div>
</div>
</div>

<form class="weather-inputs" id="environmentForm" data-endpoint="<?= site_url('environment') ?>">
<div class="weather-input">
<label for="tdsInput">TDS (ppm)</label>
<input type="number" step="0.1" min="0" id="tdsInput" class="form-control" placeholder="e.g. 850" value="<?= $tds !== null ? esc($tds) : '' ?>">
</div>
<div class="weather-input">
<label for="phInput">pH Level</label>
<input type="number" step="0.1" min="0" max="14" id="phInput" class="form-control" placeholder="e.g. 6.2" value="<?= $ph !== null ? esc($ph) : '' ?>">
</div>
<div class="weather-input weather-input-actions">
<button type="submit" class="btn btn-success">Save</button>
<small id="environmentStatus" class="text-muted"></small>
</div>
</form>
</div>

<!-- CARDS -->

<div class="row mb-4">

<div class="col-md-3">
<div class="card card-box p-3">
<h6>Total Plants</h6>
<h3 id="totalPlants">0</h3>
</div>
</div>

<div class="col-md-3">
<div class="card card-box p-3">
<h6>Growing</h6>
<h3 class="text-warning" id="growingCount">0</h3>
</div>
</div>

<div class="col-md-3">
<div class="card card-box p-3">
<h6>Ready</h6>
<h3 class="text-success" id="readyCount">0</h3>
</div>
</div>

<div class="col-md-3">
<div class="card card-box p-3">
<h6>Harvested</h6>
<h3 class="text-secondary" id="harvestedCount">0</h3>
</div>
</div>

</div>

<!-- CHART + AI PANEL -->

<div class="row mb-4">

<div class="col-md-6">

<div class="card card-box p-4">
<h5>Lettuce Growth Prediction</h5>
<canvas id="growthChart"></canvas>
</div>

</div>

<div class="col-md-6">

<div class="ai-box" id="aiPredictionBox" data-ml-endpoint="<?= esc($mlEndpoint) ?>" data-plant-id="<?= esc((string) ($aiPlantId ?? '')) ?>">

<h5>AI Harvest Prediction</h5>

<div class="mb-2">
<label for="aiPlantSelect" class="form-label">Select Plant</label>
<select id="aiPlantSelect" class="form-select">
<option value="" disabled <?= $aiPlantId === null ? 'selected' : '' ?>>Choose a plant</option>
<?php foreach ($aiPlantOptions as $plant): ?>
<option value="<?= esc($plant['id']) ?>" <?= ((string) $plant['id'] === (string) $aiPlantId) ? 'selected' : '' ?>>
<?= esc($plant['variety']) ?> (#<?= esc($plant['id']) ?>)
</option>
<?php endforeach; ?>
</select>
</div>
<div class="prediction-actions d-flex gap-2 mb-2">
<button type="button" class="btn btn-outline-success btn-sm" id="predictAllBtn">Predict All Plants</button>
</div>

<p><b>Variety:</b> <span id="aiVariety"><?= esc($aiVariety) ?></span></p>
<p><b>Date Planted:</b> <span id="aiPlantDate"><?= esc($aiPlantDate) ?></span></p>

<p><b>Estimated Harvest:</b> <span id="aiHarvest"></span></p>
<p class="text-muted" id="aiPredictionStatus"></p>

<p><b>Prediction Confidence:</b></p>

<div class="confidence-bar">
<div class="confidence-fill" id="confidenceFill"></div>
</div>

<p class="mt-2"><span id="confidenceText"></span></p>

</div>

</div>

</div>

<!-- CALENDAR -->

<div class="row mb-4">

<div class="col-md-12">

<div class="ai-box">

<h5>Harvest Calendar</h5>

<div id="calendar"></div>

</div>

</div>

</div>

<!-- TABLE -->

<div class="table-container">

<div class="table-header mb-3">

<h5>Lettuce Records</h5>

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

<?php
$calendarEvents = [];
foreach (($plants ?? []) as $plant) {
    $calendarEvents[] = [
        'id' => $plant['id'],
        'title' => 'Plant Harvest: ' . $plant['variety'],
        'date' => $plant['predicted_harvest'],
    ];
}
$plantSummaries = [];
foreach (($plants ?? []) as $plant) {
    $plantSummaries[] = [
        'id' => $plant['id'],
        'date_planted' => $plant['date_planted'],
    ];
}
?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>

/* WEATHER */

const weatherCard = document.querySelector(".weather-card");
const weatherEndpoint = weatherCard ? weatherCard.dataset.weatherEndpoint : null;
const weatherLocationText = document.getElementById("weatherLocationText");
const weatherTempText = document.getElementById("weatherTempText");
const weatherConditionText = document.getElementById("weatherConditionText");
const weatherTempValue = document.getElementById("weatherTempValue");
const weatherHumidityValue = document.getElementById("weatherHumidityValue");
const weatherTdsValue = document.getElementById("weatherTdsValue");
const weatherPhValue = document.getElementById("weatherPhValue");
const fallbackCity = "Cebu";
const reverseGeocodeEndpoint = "https://api.bigdatacloud.net/data/reverse-geocode-client";
const environmentForm = document.getElementById("environmentForm");
const environmentStatus = document.getElementById("environmentStatus");
const tdsInput = document.getElementById("tdsInput");
const phInput = document.getElementById("phInput");
const aiPredictionBox = document.getElementById("aiPredictionBox");
const mlEndpoint = aiPredictionBox ? aiPredictionBox.dataset.mlEndpoint : "";
let aiPlantId = aiPredictionBox ? aiPredictionBox.dataset.plantId : "";
const aiPredictionStatus = document.getElementById("aiPredictionStatus");
const predictSaveBase = "<?= site_url('plants') ?>";
const aiPlantSelect = document.getElementById("aiPlantSelect");
const aiVarietyText = document.getElementById("aiVariety");
const aiPlantDateText = document.getElementById("aiPlantDate");
const predictAllBtn = document.getElementById("predictAllBtn");
const calendarEvents = <?= json_encode($calendarEvents, JSON_UNESCAPED_SLASHES) ?>;
const plantSummaries = <?= json_encode($plantSummaries, JSON_UNESCAPED_SLASHES) ?>;
const aiPlantOptions = <?= json_encode($aiPlantOptions, JSON_UNESCAPED_SLASHES) ?>;

let latestWeather = {
temperature_c: null,
humidity_pct: null
};

let growthChart = null;
let isBatchPredicting = false;

function setWeatherText(el, value){
if(!el){
return;
}
el.textContent=value;
}

function formatTemp(value){
if(value===null||value===undefined||Number.isNaN(Number(value))){
return "--";
}
let rounded=Math.round(Number(value)*10)/10;
return `${rounded}°C`;
}

function formatPercent(value){
if(value===null||value===undefined||Number.isNaN(Number(value))){
return "--";
}
return `${Math.round(Number(value))}%`;
}

function formatTds(value){
if(value===null||value===undefined||Number.isNaN(Number(value))){
return "--";
}
return `${Math.round(Number(value))} ppm`;
}

function formatPh(value){
if(value===null||value===undefined||Number.isNaN(Number(value))){
return "--";
}
let rounded=Math.round(Number(value)*10)/10;
return `${rounded}`;
}

function parseNumber(value){
if(value===null||value===undefined){
return null;
}
let cleaned=String(value).replace(/[^0-9.\\-]/g,"");
if(cleaned===""){
return null;
}
let num=Number(cleaned);
return Number.isNaN(num) ? null : num;
}

function computeGrowthDays(dateText){
let planted=new Date(dateText);
if(Number.isNaN(planted.getTime())){
return null;
}
let now=new Date();
let diff=(now-planted)/(1000*60*60*24);
return diff<0 ? 0 : Math.floor(diff);
}

function updateGrowthChartWithEstimate(totalDays, harvestDate){
if(!growthChart){
return;
}
let safeTotalDays=Math.max(1, Math.round(totalDays));
let labels=[];
for(let i=1;i<=4;i++){
let milestoneDays=Math.round(safeTotalDays*(i/4));
let week=Math.max(1, Math.round(milestoneDays/7));
labels.push(`Week ${week}`);
}
labels.push(`Harvest (${harvestDate.toDateString()})`);
growthChart.data.labels=labels;
growthChart.data.datasets[0].data=[25,50,75,90,100];
growthChart.update();
}

function updateCalendarForPlant(plantId, harvestDate){
if(!calendar || !plantId){
return;
}
let event=calendar.getEventById(String(plantId));
let iso=harvestDate.toISOString().slice(0,10);
if(event){
event.setStart(iso);
}else{
calendar.addEvent({ id:String(plantId), title:"Plant Harvest", date:iso });
}
}

function updateTableHarvestDate(plantId, harvestDate){
if(!plantId){
return;
}
let row=[...document.querySelectorAll("#tableBody tr")].find(r=>r.firstElementChild && r.firstElementChild.innerText.trim()===String(plantId));
if(!row){
return;
}
let cell=row.querySelector(".harvestDate");
if(cell){
cell.innerText=harvestDate.toISOString().slice(0,10);
}
updateStatusAndCounts();
}

async function persistHarvestDate(plantId, harvestDate){
if(!plantId){
return;
}
let endpoint=`${predictSaveBase}/${plantId}/predict`;
try{
await fetch(endpoint,{
method:"POST",
headers:{ "Content-Type":"application/json" },
body:JSON.stringify({
predicted_harvest: harvestDate.toISOString().slice(0,10)
})
});
}catch(err){
// non-blocking
}
}

async function predictForPlant(plant){
let growthDays=computeGrowthDays(plant.date_planted);
if(growthDays===null){
return null;
}
let tdsValue=parseNumber(tdsInput ? tdsInput.value : (weatherTdsValue ? weatherTdsValue.textContent : null));
let phValue=parseNumber(phInput ? phInput.value : (weatherPhValue ? weatherPhValue.textContent : null));
let temperatureC=latestWeather.temperature_c;
let humidityPct=latestWeather.humidity_pct;

if(temperatureC===null||humidityPct===null||tdsValue===null||phValue===null){
return null;
}

let response=await fetch(mlEndpoint,{
method:"POST",
headers:{ "Content-Type":"application/json" },
body:JSON.stringify({
temperature_c: temperatureC,
humidity_pct: humidityPct,
tds_ppm: tdsValue,
ph_level: phValue,
growth_days: growthDays
})
});
let data=await response.json();
if(!response.ok||!data.ok){
return null;
}
let days=Math.max(0, Math.round(Number(data.days_to_harvest)));
let harvestDate=new Date();
harvestDate.setDate(harvestDate.getDate()+days);
return { harvestDate, growthDays, days };
}

async function updateAllPlantPredictions(){
if(isBatchPredicting || !mlEndpoint || !Array.isArray(plantSummaries) || plantSummaries.length === 0){
return;
}
isBatchPredicting=true;
let completed=0;
let total=plantSummaries.length;
if(aiPredictionStatus){
aiPredictionStatus.textContent=`Updating ${total} plants...`;
}

const concurrency=3;
let index=0;

async function worker(){
while(index < total){
let currentIndex=index++;
let plant=plantSummaries[currentIndex];
let result=await predictForPlant(plant);
if(result && plant.id){
updateCalendarForPlant(plant.id, result.harvestDate);
updateTableHarvestDate(plant.id, result.harvestDate);
persistHarvestDate(plant.id, result.harvestDate);
}
completed++;
if(aiPredictionStatus){
aiPredictionStatus.textContent=`Updated ${completed}/${total} plants...`;
}
}
}

let workers=[];
for(let i=0;i<Math.min(concurrency,total);i++){
workers.push(worker());
}
await Promise.all(workers);

if(aiPredictionStatus){
aiPredictionStatus.textContent="All plant predictions updated.";
}
isBatchPredicting=false;
}

async function updateAiPrediction(){
if(!mlEndpoint){
if(aiPredictionStatus){
aiPredictionStatus.textContent="ML endpoint not configured.";
}
return;
}
if(!aiPlantId){
if(aiPredictionStatus){
aiPredictionStatus.textContent="Select a plant to predict.";
}
return;
}
let growthDays=computeGrowthDays(document.getElementById("aiPlantDate").textContent.trim());
let tdsValue=parseNumber(tdsInput ? tdsInput.value : (weatherTdsValue ? weatherTdsValue.textContent : null));
let phValue=parseNumber(phInput ? phInput.value : (weatherPhValue ? weatherPhValue.textContent : null));
let temperatureC=latestWeather.temperature_c;
let humidityPct=latestWeather.humidity_pct;

if(growthDays===null||temperatureC===null||humidityPct===null||tdsValue===null||phValue===null){
if(aiPredictionStatus){
aiPredictionStatus.textContent="Waiting for complete data to predict.";
}
return;
}

if(aiPredictionStatus){
aiPredictionStatus.textContent="Predicting...";
}

try{
let response=await fetch(mlEndpoint,{
method:"POST",
headers:{ "Content-Type":"application/json" },
body:JSON.stringify({
temperature_c: temperatureC,
humidity_pct: humidityPct,
tds_ppm: tdsValue,
ph_level: phValue,
growth_days: growthDays
})
});
let data=await response.json();
if(!response.ok||!data.ok){
throw new Error(data.error||"Prediction failed.");
}
let days=Math.max(0, Math.round(Number(data.days_to_harvest)));
let harvestDate=new Date();
harvestDate.setDate(harvestDate.getDate()+days);
let harvestText=`In ${days} days (${harvestDate.toDateString()})`;
document.getElementById("aiHarvest").textContent=harvestText;
updateGrowthChartWithEstimate(growthDays + days, harvestDate);
updateCalendarForPlant(aiPlantId, harvestDate);
updateTableHarvestDate(aiPlantId, harvestDate);
persistHarvestDate(aiPlantId, harvestDate);
if(aiPredictionStatus){
aiPredictionStatus.textContent="Prediction updated.";
}
}catch(err){
if(aiPredictionStatus){
aiPredictionStatus.textContent="Prediction failed.";
}
}
}

function syncAiPanelWithPlant(plantId){
let plant=aiPlantOptions.find(p=>String(p.id)===String(plantId));
if(!plant){
return;
}
aiPlantId=String(plant.id);
if(aiPredictionBox){
aiPredictionBox.dataset.plantId=aiPlantId;
}
if(aiVarietyText){
aiVarietyText.textContent=plant.variety;
}
if(aiPlantDateText){
aiPlantDateText.textContent=plant.date_planted;
}
updateAiPrediction();
}

function buildLocationLabel(parts){
let unique=[];
let seen={};
parts.forEach(part=>{
if(!part){
return;
}
let key=String(part).trim().toLowerCase();
if(!key||seen[key]){
return;
}
seen[key]=true;
unique.push(part);
});
return unique.join(", ");
}

async function reverseGeocode(lat, lon){
if(!reverseGeocodeEndpoint){
return;
}
try{
let url=new URL(reverseGeocodeEndpoint);
url.searchParams.set("latitude", lat);
url.searchParams.set("longitude", lon);
url.searchParams.set("localityLanguage", "en");
let response=await fetch(url.toString());
let data=await response.json();
if(!response.ok){
throw new Error("Reverse geocode failed.");
}
let locationLabel=buildLocationLabel([
data.locality,
data.city,
data.principalSubdivision,
data.countryName
]);
if(locationLabel){
setWeatherText(weatherLocationText, locationLabel);
}
}catch(err){
// Keep whatever weather location text is already shown.
}
}

async function loadWeatherForLocation(lat, lon){
if(!weatherEndpoint){
return;
}
try{
let response=await fetch(weatherEndpoint,{
method:"POST",
headers:{ "Content-Type":"application/json" },
body:JSON.stringify({ lat, lon })
});
let data=await response.json();
if(!response.ok||!data.ok){
throw new Error(data.error||"Failed to load weather.");
}
setWeatherText(weatherLocationText, data.location || "Current Location");
setWeatherText(weatherTempText, formatTemp(data.temperature_c));
setWeatherText(weatherTempValue, formatTemp(data.temperature_c));
setWeatherText(weatherHumidityValue, formatPercent(data.humidity_pct));
setWeatherText(weatherConditionText, data.condition || "");
latestWeather.temperature_c=parseNumber(data.temperature_c);
latestWeather.humidity_pct=parseNumber(data.humidity_pct);
updateAiPrediction();
}catch(err){
setWeatherText(weatherLocationText, "Weather unavailable");
}
}

async function loadWeatherForCity(city){
if(!weatherEndpoint){
return;
}
try{
let response=await fetch(weatherEndpoint,{
method:"POST",
headers:{ "Content-Type":"application/json" },
body:JSON.stringify({ city })
});
let data=await response.json();
if(!response.ok||!data.ok){
throw new Error(data.error||"Failed to load weather.");
}
setWeatherText(weatherLocationText, data.location || city);
setWeatherText(weatherTempText, formatTemp(data.temperature_c));
setWeatherText(weatherTempValue, formatTemp(data.temperature_c));
setWeatherText(weatherHumidityValue, formatPercent(data.humidity_pct));
setWeatherText(weatherConditionText, data.condition || "");
latestWeather.temperature_c=parseNumber(data.temperature_c);
latestWeather.humidity_pct=parseNumber(data.humidity_pct);
updateAiPrediction();
}catch(err){
setWeatherText(weatherLocationText, "Weather unavailable");
}
}

if(weatherEndpoint && navigator.geolocation){
setWeatherText(weatherLocationText, "Locating...");
navigator.geolocation.getCurrentPosition(
position=>{
reverseGeocode(position.coords.latitude, position.coords.longitude);
loadWeatherForLocation(position.coords.latitude, position.coords.longitude);
},
error=>{
loadWeatherForCity(fallbackCity);
},
{ enableHighAccuracy:true, timeout:10000, maximumAge:600000 }
);
}else if(weatherEndpoint){
loadWeatherForCity(fallbackCity);
}

if(environmentForm){
environmentForm.addEventListener("submit",async function(e){
e.preventDefault();
if(environmentStatus){
environmentStatus.textContent="Saving...";
}
try{
let response=await fetch(this.dataset.endpoint,{
method:"POST",
headers:{ "Content-Type":"application/json" },
body:JSON.stringify({
tds_ppm: tdsInput.value.trim(),
ph_level: phInput.value.trim()
})
});
let data=await response.json();
if(!response.ok||!data.ok){
throw new Error(data.error||"Failed to save.");
}
setWeatherText(weatherTdsValue, formatTds(data.tds_ppm));
setWeatherText(weatherPhValue, formatPh(data.ph_level));
if(environmentStatus){
environmentStatus.textContent="Saved";
}
updateAiPrediction();
}catch(err){
if(environmentStatus){
environmentStatus.textContent="Save failed";
}
}
});
}

if(aiPlantSelect){
aiPlantSelect.addEventListener("change",function(){
syncAiPanelWithPlant(this.value);
});
if(aiPlantSelect.value){
syncAiPanelWithPlant(aiPlantSelect.value);
}
}

if(predictAllBtn){
predictAllBtn.addEventListener("click",function(){
updateAllPlantPredictions();
});
}

/* SEARCH */

document.getElementById("searchInput").addEventListener("keyup",function(){
let value=this.value.toLowerCase();
document.querySelectorAll("#tableBody tr").forEach(row=>{
row.style.display=row.innerText.toLowerCase().includes(value)?"":"none";
});
});

/* STATUS AUTO */

function updateStatusAndCounts(){
let today=new Date();
let growing=0,ready=0,harvested=0;

document.querySelectorAll("#tableBody tr").forEach(row=>{
let harvest=row.querySelector(".harvestDate").innerText;
let harvestDate=new Date(harvest);
let statusCell=row.querySelector(".statusCell");

let diff=(harvestDate-today)/(1000*60*60*24);

if(diff>7){
statusCell.innerHTML='<span class="status growing">Growing</span>';
growing++;
}else if(diff>=0){
statusCell.innerHTML='<span class="status ready">Ready</span>';
ready++;
}else{
statusCell.innerHTML='<span class="status harvested">Harvested</span>';
harvested++;
}
});

document.getElementById("totalPlants").innerText=document.querySelectorAll("#tableBody tr").length;
document.getElementById("growingCount").innerText=growing;
document.getElementById("readyCount").innerText=ready;
document.getElementById("harvestedCount").innerText=harvested;
}

updateStatusAndCounts();

/* AI PREDICTION MODEL (simple simulation) */

let plantDate=new Date("2026-03-01");
let growthDays=30;

let harvestDate=new Date(plantDate);
harvestDate.setDate(plantDate.getDate()+growthDays);

document.getElementById("aiHarvest").innerText=harvestDate.toDateString();

let confidence=Math.floor(Math.random()*10)+90;

document.getElementById("confidenceText").innerText=confidence+"% confidence";
document.getElementById("confidenceFill").style.width=confidence+"%";

/* CHART */

growthChart=new Chart(document.getElementById("growthChart"),{
type:'line',
data:{
labels:["Week 1","Week 2","Week 3","Week 4","Harvest"],
datasets:[{
label:"Predicted Growth %",
data:[10,35,60,85,100],
borderColor:"#2e7d32",
backgroundColor:"rgba(46,125,50,0.2)",
fill:true
}]
}
});

/* CALENDAR */

let calendar;

document.addEventListener('DOMContentLoaded',function(){

let calendarEl=document.getElementById('calendar');

calendar=new FullCalendar.Calendar(calendarEl,{
initialView:'dayGridMonth',
events:calendarEvents
});

calendar.render();

});

/* ADD PLANT MODAL */

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

updateStatusAndCounts();

if(calendar){
calendar.addEvent({ id:String(plant.id), title:`Plant Harvest: ${plant.variety}`, date:plant.predicted_harvest });
}

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
updateStatusAndCounts();
if(calendar){
let event=calendar.getEventById(String(id));
if(event){
event.remove();
}
}
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

updateStatusAndCounts();

if(calendar){
let event=calendar.getEventById(String(plant.id));
if(event){
event.setProp("title",`Plant Harvest: ${plant.variety}`);
event.setStart(plant.predicted_harvest);
}else{
calendar.addEvent({ id:String(plant.id), title:`Plant Harvest: ${plant.variety}`, date:plant.predicted_harvest });
}
}

editPlantModal.hide();
}catch(err){
showFormErrors(editPlantErrors,"Network error while updating. Please try again.");
}
});

</script>
<?= $this->endSection() ?>
