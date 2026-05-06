<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$latestEnv = ($plants ?? [])[0] ?? null;
$aiPlantDate = $latestEnv['date_planted'] ?? date('Y-m-d');
$aiVariety = $latestEnv['variety'] ?? 'Romaine';
$aiPlantId = $latestEnv['id'] ?? null;
$mlEndpoint = getenv('ML_PREDICT_URL') ?: '';
$weatherEndpoint = site_url('weather');
$aiPlantOptions = [];
foreach (($plants ?? []) as $plant) {
    $aiPlantOptions[] = [
        'id' => $plant['id'],
        'variety' => $plant['variety'],
        'date_planted' => $plant['date_planted'],
        'tds_ppm' => $plant['tds_ppm'] ?? null,
        'ph_level' => $plant['ph_level'] ?? null,
    ];
}
$plantSummaries = [];
foreach (($plants ?? []) as $plant) {
    $plantSummaries[] = [
        'id' => $plant['id'],
        'date_planted' => $plant['date_planted'],
        'predicted_harvest' => $plant['predicted_harvest'] ?? null,
        'variety' => $plant['variety'],
    ];
}
?>

<h3 class="mb-4">Lettuce Growth Predictions</h3>

<div class="row mb-4">
<div class="col-lg-7">
<div class="card card-box p-4">
<h5>Lettuce Growth Prediction</h5>
<canvas id="growthChart"></canvas>
</div>
</div>

<div class="col-lg-5">
<div class="ai-box h-100" id="aiPredictionBox" data-ml-endpoint="<?= esc($mlEndpoint) ?>" data-plant-id="<?= esc((string) ($aiPlantId ?? '')) ?>" data-weather-endpoint="<?= esc($weatherEndpoint) ?>">
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

<p><b>Variety:</b> <span id="aiVariety"><?= esc($aiVariety) ?></span></p>
<p><b>Date Planted:</b> <span id="aiPlantDate"><?= esc($aiPlantDate) ?></span></p>
<div class="row g-2 mb-3">
<div class="col-6">
<label for="aiTempInput" class="form-label">Temperature (°C)</label>
<input type="number" step="0.1" id="aiTempInput" class="form-control" placeholder="e.g. 30.5">
</div>
<div class="col-6">
<label for="aiHumidityInput" class="form-label">Humidity (%)</label>
<input type="number" step="0.1" id="aiHumidityInput" class="form-control" placeholder="e.g. 60">
</div>
<div class="col-6">
<label for="aiTdsInput" class="form-label">TDS (ppm)</label>
<input type="number" step="0.1" id="aiTdsInput" class="form-control" placeholder="e.g. 700">
</div>
<div class="col-6">
<label for="aiPhInput" class="form-label">pH Level</label>
<input type="number" step="0.1" id="aiPhInput" class="form-control" placeholder="e.g. 6.2">
</div>
</div>
<div class="mb-3">
<button type="button" class="btn btn-outline-secondary btn-sm" id="useWeatherBtn">Use Weather</button>
</div>

<div class="prediction-actions d-flex gap-2 mb-2">
<button type="button" class="btn btn-success btn-sm" id="predictOneBtn">Predict Selected</button>
<button type="button" class="btn btn-outline-success btn-sm" id="predictAllBtn">Predict All Plants</button>
</div>

<p><b>Estimated Harvest:</b> <span id="aiHarvest"></span></p>
<p class="text-muted" id="aiPredictionStatus"></p>
</div>
</div>
</div>

<div class="row mb-4">
<div class="col-12">
<div class="ai-box">
<h5>Harvest Calendar</h5>
<div id="calendar"></div>
</div>
</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
const aiPredictionBox = document.getElementById("aiPredictionBox");
const mlEndpoint = aiPredictionBox ? aiPredictionBox.dataset.mlEndpoint : "";
const weatherEndpoint = aiPredictionBox ? aiPredictionBox.dataset.weatherEndpoint : "";
let aiPlantId = aiPredictionBox ? aiPredictionBox.dataset.plantId : "";
const predictSaveBase = "<?= site_url('plants') ?>";
const aiPlantSelect = document.getElementById("aiPlantSelect");
const aiVarietyText = document.getElementById("aiVariety");
const aiPlantDateText = document.getElementById("aiPlantDate");
const aiPredictionStatus = document.getElementById("aiPredictionStatus");
const aiHarvestText = document.getElementById("aiHarvest");
const aiTempInput = document.getElementById("aiTempInput");
const aiHumidityInput = document.getElementById("aiHumidityInput");
const aiTdsInput = document.getElementById("aiTdsInput");
const aiPhInput = document.getElementById("aiPhInput");
const useWeatherBtn = document.getElementById("useWeatherBtn");
const predictOneBtn = document.getElementById("predictOneBtn");
const predictAllBtn = document.getElementById("predictAllBtn");
const plantSummaries = <?= json_encode($plantSummaries, JSON_UNESCAPED_SLASHES) ?>;
const aiPlantOptions = <?= json_encode($aiPlantOptions, JSON_UNESCAPED_SLASHES) ?>;
const weatherLocationText = null;
const weatherTempText = null;
const weatherConditionText = null;
const weatherTempValue = null;
const weatherHumidityValue = null;
const fallbackCity = "Cebu";
const reverseGeocodeEndpoint = "https://api.bigdatacloud.net/data/reverse-geocode-client";

let growthChart = null;
let isBatchPredicting = false;
let currentPlantData = null;
let latestWeather = { temperature_c: null, humidity_pct: null };
let calendar = null;

function parseNumber(value){
if(value===null||value===undefined){
return null;
}
let cleaned=String(value).replace(/[^0-9.\-]/g,"");
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

function setText(el, value){
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
// no visible weather location on this page
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
latestWeather.temperature_c=parseNumber(data.temperature_c);
latestWeather.humidity_pct=parseNumber(data.humidity_pct);
if(aiTempInput && !aiTempInput.value){
aiTempInput.value=latestWeather.temperature_c ?? "";
}
if(aiHumidityInput && !aiHumidityInput.value){
aiHumidityInput.value=latestWeather.humidity_pct ?? "";
}
}catch(err){
// keep inputs as-is
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
latestWeather.temperature_c=parseNumber(data.temperature_c);
latestWeather.humidity_pct=parseNumber(data.humidity_pct);
if(aiTempInput && !aiTempInput.value){
aiTempInput.value=latestWeather.temperature_c ?? "";
}
if(aiHumidityInput && !aiHumidityInput.value){
aiHumidityInput.value=latestWeather.humidity_pct ?? "";
}
}catch(err){
// keep inputs as-is
}
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
let plant=plantSummaries.find(p=>String(p.id)===String(plantId));
let title=plant ? `Plant Harvest: ${plant.variety}` : "Plant Harvest";
if(event){
event.setStart(iso);
event.setProp("title", title);
}else{
calendar.addEvent({ id:String(plantId), title, date:iso });
}
}

function updateCalendarFromExisting(){
if(!calendar){
return;
}
calendar.addEventSource(plantSummaries.map(p=>({
id:String(p.id),
title:`Plant Harvest: ${p.variety}`,
date:p.predicted_harvest || p.date_planted
})));
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
let temperatureC=parseNumber(aiTempInput ? aiTempInput.value : null);
let humidityPct=parseNumber(aiHumidityInput ? aiHumidityInput.value : null);
let tdsValue=parseNumber(aiTdsInput ? aiTdsInput.value : plant.tds_ppm);
let phValue=parseNumber(aiPhInput ? aiPhInput.value : plant.ph_level);

if(temperatureC===null){
temperatureC=latestWeather.temperature_c;
}
if(humidityPct===null){
humidityPct=latestWeather.humidity_pct;
}

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

async function updateAiPrediction(){
if(!mlEndpoint){
aiPredictionStatus.textContent="ML endpoint not configured.";
return;
}
if(!aiPlantId){
aiPredictionStatus.textContent="Select a plant to predict.";
return;
}
let result=await predictForPlant({
id: aiPlantId,
date_planted: aiPlantDateText.textContent.trim(),
tds_ppm: currentPlantData ? currentPlantData.tds_ppm : null,
ph_level: currentPlantData ? currentPlantData.ph_level : null
});
if(!result){
aiPredictionStatus.textContent="Prediction failed. Check inputs.";
return;
}
let harvestText=`In ${result.days} days (${result.harvestDate.toDateString()})`;
aiHarvestText.textContent=harvestText;
updateGrowthChartWithEstimate(result.growthDays + result.days, result.harvestDate);
persistHarvestDate(aiPlantId, result.harvestDate);
updateCalendarForPlant(aiPlantId, result.harvestDate);
aiPredictionStatus.textContent="Prediction updated.";
}

async function updateAllPlantPredictions(){
if(isBatchPredicting || !mlEndpoint || !Array.isArray(plantSummaries) || plantSummaries.length === 0){
return;
}
isBatchPredicting=true;
let completed=0;
let total=plantSummaries.length;
aiPredictionStatus.textContent=`Updating ${total} plants...`;

const concurrency=3;
let index=0;

async function worker(){
while(index < total){
let currentIndex=index++;
let plant=plantSummaries[currentIndex];
let plantWithEnv=aiPlantOptions.find(p=>String(p.id)===String(plant.id)) || plant;
let result=await predictForPlant(plantWithEnv);
if(result && plant.id){
persistHarvestDate(plant.id, result.harvestDate);
updateCalendarForPlant(plant.id, result.harvestDate);
}
completed++;
aiPredictionStatus.textContent=`Updated ${completed}/${total} plants...`;
}
}

let workers=[];
for(let i=0;i<Math.min(concurrency,total);i++){
workers.push(worker());
}
await Promise.all(workers);
aiPredictionStatus.textContent="All plant predictions updated.";
isBatchPredicting=false;
}

function syncAiPanelWithPlant(plantId){
let plant=aiPlantOptions.find(p=>String(p.id)===String(plantId));
if(!plant){
return;
}
currentPlantData = plant;
aiPlantId=String(plant.id);
if(aiPredictionBox){
aiPredictionBox.dataset.plantId=aiPlantId;
}
aiVarietyText.textContent=plant.variety;
aiPlantDateText.textContent=plant.date_planted;
if(aiTdsInput){
aiTdsInput.value=plant.tds_ppm ?? "";
}
if(aiPhInput){
aiPhInput.value=plant.ph_level ?? "";
}
}

if(aiPlantSelect){
aiPlantSelect.addEventListener("change",function(){
syncAiPanelWithPlant(this.value);
});
if(aiPlantSelect.value){
syncAiPanelWithPlant(aiPlantSelect.value);
}
}

if(predictOneBtn){
predictOneBtn.addEventListener("click",function(){
updateAiPrediction();
});
}

if(predictAllBtn){
predictAllBtn.addEventListener("click",function(){
updateAllPlantPredictions();
});
}

if(useWeatherBtn){
useWeatherBtn.addEventListener("click",function(){
if(latestWeather.temperature_c !== null && aiTempInput){
aiTempInput.value = latestWeather.temperature_c;
}
if(latestWeather.humidity_pct !== null && aiHumidityInput){
aiHumidityInput.value = latestWeather.humidity_pct;
}
});
}

if(weatherEndpoint && navigator.geolocation){
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

document.addEventListener('DOMContentLoaded',function(){
let calendarEl=document.getElementById('calendar');
calendar=new FullCalendar.Calendar(calendarEl,{
initialView:'dayGridMonth',
events: []
});
calendar.render();
updateCalendarFromExisting();
});
</script>
<?= $this->endSection() ?>
