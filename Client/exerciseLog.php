<?php
//this is a demo file it is not used in website
    include("../connection.php");
    session_start();
    if(!isset($_SESSION["user_id"])){
    header("location:../login.php");
    exit();
    }
    $user_id = $_SESSION["user_id"];

    $stmt = $conn -> prepare("SELECT * FROM client WHERE user_id =?");
    $stmt -> bind_param("s",$user_id);
    $stmt -> execute();
    $result = $stmt -> get_result();
    $data = $result -> fetch_assoc();

    if($result->num_rows === 1){

    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>exercise log</title>
    <link rel="stylesheet" href="exerciseLog.css">
</head>
<!-- <body>
    
    <form  method="post">

    <div class="exercise" id="walking">
        <input type="checkbox" name="Walking" id="Walking" value="Walking">Walking
        <select name="intensity" id="intensity">
            <option value="" disabled selected>Select intensity</option>
            <option value="low">Low</option>
            <option value="moderate">Moderate</option>
            <option value="high">High</option>
            <option value="very High">Very High</option>
        </select>
        <input type="number" placeholder="Minutes">
        <span class="duration-text">min</span>
    </div>
    <div class="exercise" id="Running">
        <input type="checkbox" name="Running" id="Running" value="Running">Running 
        <select name="intensity" id="intensity">
            <option value="" disabled selected>Select intensity</option>
            <option value="low">Low</option>
            <option value="moderate">Moderate</option>
            <option value="high">High</option>
            <option value="very High">Very High</option>
        </select>
        <input type="number" placeholder="Minutes">
        <span class="duration-text">min</span>
    </div>
    <div class="exercise" id="Cycling">
        <input type="checkbox" name="Cycling" id="Cycling" value="Cycling">Cycling 
        <select name="intensity" id="intensity">
            <option value="" disabled selected>Select intensity</option>
            <option value="low">Low</option>
            <option value="moderate">Moderate</option>
            <option value="high">High</option>
            <option value="very High">Very High</option>
        </select>
        <input type="number" placeholder="Minutes">
        <span class="duration-text">min</span>
    </div>
    <div class="exercise" id="Skipping / Jump Rope" id="Skipping / Jump Rope">
        <input type="checkbox" name="Skipping / Jump Rope" id="Skipping / Jump Rope" value="Skipping / Jump Rope">Skipping / Jump Rope 
        <select name="intensity" id="intensity">
            <option value="" disabled selected>Select intensity</option>
            <option value="low">Low</option>
            <option value="moderate">Moderate</option>
            <option value="high">High</option>
            <option value="very High">Very High</option>
        </select>
        <input type="number" placeholder="Minutes">
        <span class="duration-text">min</span>
    </div>
    <div class="exercise" id="Weight Training (Light)">
        <input type="checkbox" name="Weight Training (Light)" id="Weight Training (Light)" value="Weight Training (Light)">Weight Training (Light) 
        <select name="intensity" id="intensity">
            <option value="" disabled selected>Select intensity</option>
            <option value="low">Low</option>
            <option value="moderate">Moderate</option>
            <option value="high">High</option>
            <option value="very High">Very High</option>
        </select>
        <input type="number" placeholder="Minutes">
        <span class="duration-text">min</span>
    </div>
    <div class="exercise" id="Weight Training (Heavy)">
        <input type="checkbox" name="Weight Training (Heavy)" id="Weight Training (Heavy)" value="Weight Training (Heavy)">Weight Training (Heavy) 
        <select name="intensity" id="intensity">
            <option value="" disabled selected>Select intensity</option>
            <option value="low">Low</option>
            <option value="moderate">Moderate</option>
            <option value="high">High</option>
            <option value="very High">Very High</option>
        </select>
        <input type="number" placeholder="Minutes">
        <span class="duration-text">min</span>
    </div>
    <div class="exercise" id="Push-ups">
        <input type="checkbox" name="Push-ups" id="Push-ups" value="Push-ups">Push-ups 
        <select name="intensity" id="intensity">
            <option value="" disabled selected>Select intensity</option>
            <option value="low">Low</option>
            <option value="moderate">Moderate</option>
            <option value="high">High</option>
            <option value="very High">Very High</option>
        </select>
        <input type="number" placeholder="Minutes">
        <span class="duration-text">min</span>
    </div>
    <div class="exercise" id="Squats">
        <input type="checkbox" name="Squats" id="Squats" value="Squats">Squats 
        <select name="intensity" id="intensity">
            <option value="" disabled selected>Select intensity</option>
            <option value="low">Low</option>
            <option value="moderate">Moderate</option>
            <option value="high">High</option>
            <option value="very High">Very High</option>
        </select>
        <input type="number" placeholder="Minutes">
        <span class="duration-text">min</span>
    </div>
    <div class="exercise" id="Yoga">
        <input type="checkbox" name="Yoga" id="Yoga" value="Yoga">Yoga 
        <select name="intensity" id="intensity">
            <option value="" disabled selected>Select intensity</option>
            <option value="low">Low</option>
            <option value="moderate">Moderate</option>
            <option value="high">High</option>
            <option value="very High">Very High</option>
        </select>
        <input type="number" placeholder="Minutes">
        <span class="duration-text">min</span>
    </div>
    <div class="exercise" id="Stretching">
        <input type="checkbox" name="Stretching" id="Stretching" value="Stretching">Stretching 
        <select name="intensity" id="intensity">
            <option value="" disabled selected>Select intensity</option>
            <option value="low">Low</option>
            <option value="moderate">Moderate</option>
            <option value="high">High</option>
            <option value="very High">Very High</option>
        </select>
        <input type="number" placeholder="Minutes">
        <span class="duration-text">min</span>
    </div>
    <div class="exercise" id="Pilates">
        <input type="checkbox" name="Pilates" id="Pilates" value="Pilates">Pilates 
        <select name="intensity" id="intensity">
            <option value="" disabled selected>Select intensity</option>
            <option value="low">Low</option>
            <option value="moderate">Moderate</option>
            <option value="high">High</option>
            <option value="very High">Very High</option>
        </select>
        <input type="number" placeholder="Minutes">
        <span class="duration-text">min</span>
    </div>
    <div class="exercise" id="Football (Soccer)">
        <input type="checkbox" name="Football (Soccer)" id="Football (Soccer)" value="Football (Soccer)">Football (Soccer) 
        <select name="intensity" id="intensity">
            <option value="" disabled selected>Select intensity</option>
            <option value="low">Low</option>
            <option value="moderate">Moderate</option>
            <option value="high">High</option>
            <option value="very High">Very High</option>
        </select>
        <input type="number" placeholder="Minutes">
        <span class="duration-text">min</span>
    </div>
    <div class="exercise" id="Cricket">
        <input type="checkbox" name="Cricket" id="Cricket" value="Cricket">Cricket 
        <select name="intensity" id="intensity">
            <option value="" disabled selected>Select intensity</option>
            <option value="low">Low</option>
            <option value="moderate">Moderate</option>
            <option value="high">High</option>
            <option value="very High">Very High</option>
        </select>
        <input type="number" placeholder="Minutes">
        <span class="duration-text">min</span>
    </div>
    <div class="exercise" id="Badminton">
        <input type="checkbox" name="Badminton" id="Badminton" value="Badminton">Badminton 
        <select name="intensity" id="intensity">
            <option value="" disabled selected>Select intensity</option>
            <option value="low">Low</option>
            <option value="moderate">Moderate</option>
            <option value="high">High</option>
            <option value="very High">Very High</option>
        </select>
        <input type="number" placeholder="Minutes">
        <span class="duration-text">min</span>
    </div>
    <div class="exercise" id="Basketball">
        <input type="checkbox" name="Basketball" id="Basketball" value="Basketball">Basketball 
        <select name="intensity" id="intensity">
            <option value="" disabled selected>Select intensity</option>
            <option value="low">Low</option>
            <option value="moderate">Moderate</option>
            <option value="high">High</option>
            <option value="very High">Very High</option>
        </select>
        <input type="number" placeholder="Minutes">
        <span class="duration-text">min</span>
    </div>
    <div class="exercise" id="Swimming">
        <input type="checkbox" name="Swimming" id="Swimming" value="Swimming">Swimming 
        <select name="intensity" id="intensity">
            <option value="" disabled selected>Select intensity</option>
            <option value="low">Low</option>
            <option value="moderate">Moderate</option>
            <option value="high">High</option>
            <option value="very High">Very High</option>
        </select>
        <input type="number" placeholder="Minutes">
        <span class="duration-text">min</span>
    </div>
    <div class="exercise" id="HIIT">
        <input type="checkbox" name="HIIT" id="HIIT" value="HIIT">HIIT 
        <select name="intensity" id="intensity">
            <option value="" disabled selected>Select intensity</option>
            <option value="low">Low</option>
            <option value="moderate">Moderate</option>
            <option value="high">High</option>
            <option value="very High">Very High</option>
        </select>
        <input type="number" placeholder="Minutes">
        <span class="duration-text">min</span>
    </div>
    <div class="exercise" id="Dancing">
        <input type="checkbox" name="Dancing" id="Dancing" value="Dancing">Dancing 
        <select name="intensity" id="intensity">
            <option value="" disabled selected>Select intensity</option>
            <option value="low">Low</option>
            <option value="moderate">Moderate</option>
            <option value="high">High</option>
            <option value="very High">Very High</option>
        </select>
        <input type="number" placeholder="Minutes">
        <span class="duration-text">min</span>
    </div>
    <div class="exercise" id="Stair Climbing">
        <input type="checkbox" name="Stair Climbing" id="Stair Climbing" value="Stair Climbing">Stair Climbing 
        <select name="intensity" id="intensity">
            <option value="" disabled selected>Select intensity</option>
            <option value="low">Low</option>
            <option value="moderate">Moderate</option>
            <option value="high">High</option>
            <option value="very High">Very High</option>
        </select>
        <input type="number" placeholder="Minutes">
        <span class="duration-text">min</span>
    </div>
        
        

    </form>

</body> -->

<body>

<header>
  <h1>exercise log</h1>
  <span>track calories burned · MET formula</span>
</header>

<div class="container">

  <div class="weight-row">
    <label>body weight</label>
    <input type="number" id="weight" value="70" min="1" max="300" step="0.5">
    <span style="font-family:var(--mono);font-size:0.8rem;color:var(--muted)">kg</span>
    <span style="font-family:var(--mono);font-size:0.72rem;color:var(--muted);margin-left:auto">calories = MET × 3.5 × kg ÷ 200 × min</span>
  </div>

  <div class="categories" id="cat-filters">
    <button class="cat-btn active" data-cat="all">all</button>
    <button class="cat-btn" data-cat="cardio">cardio</button>
    <button class="cat-btn" data-cat="strength">strength</button>
    <button class="cat-btn" data-cat="flexibility">flexibility</button>
    <button class="cat-btn" data-cat="sports">sports</button>
  </div>

  <div class="exercise-list" id="rows"></div>

  <div class="total-bar">
    <div>
      <div class="label">total calories burned</div>
      <div class="value" id="total-kcal">0 kcal</div>
      <div class="breakdown" id="breakdown">no exercises selected</div>
    </div>
    <button class="reset-btn" id="reset-btn">reset all</button>
  </div>

</div>

<script>
const exercises = [
  {name:"Walking",               cat:"cardio",      mets:{low:2.5,moderate:3.5,high:4.5,"very high":5.0}},
  {name:"Running",               cat:"cardio",      mets:{low:6.0,moderate:8.0,high:10.0,"very high":12.0}},
  {name:"Cycling",               cat:"cardio",      mets:{low:4.0,moderate:6.0,high:8.0,"very high":10.0}},
  {name:"Skipping / Jump Rope",  cat:"cardio",      mets:{low:8.0,moderate:10.0,high:12.0,"very high":14.0}},
  {name:"HIIT",                  cat:"cardio",      mets:{low:7.0,moderate:9.0,high:11.0,"very high":14.0}},
  {name:"Stair Climbing",        cat:"cardio",      mets:{low:4.0,moderate:6.0,high:8.0,"very high":10.0}},
  {name:"Dancing",               cat:"cardio",      mets:{low:3.0,moderate:4.5,high:6.0,"very high":8.0}},
  {name:"Swimming",              cat:"cardio",      mets:{low:5.0,moderate:7.0,high:9.0,"very high":11.0}},
  {name:"Weight Training (Light)",cat:"strength",   mets:{low:3.0,moderate:3.5,high:4.0,"very high":5.0}},
  {name:"Weight Training (Heavy)",cat:"strength",   mets:{low:4.0,moderate:5.0,high:6.0,"very high":7.0}},
  {name:"Push-ups",              cat:"strength",    mets:{low:3.5,moderate:4.5,high:5.5,"very high":7.0}},
  {name:"Squats",                cat:"strength",    mets:{low:3.5,moderate:4.5,high:5.5,"very high":7.0}},
  {name:"Yoga",                  cat:"flexibility", mets:{low:2.0,moderate:3.0,high:4.0,"very high":5.0}},
  {name:"Stretching",            cat:"flexibility", mets:{low:2.0,moderate:2.5,high:3.0,"very high":3.5}},
  {name:"Pilates",               cat:"flexibility", mets:{low:2.5,moderate:3.5,high:4.5,"very high":5.5}},
  {name:"Football (Soccer)",     cat:"sports",      mets:{low:5.0,moderate:7.0,high:9.0,"very high":11.0}},
  {name:"Cricket",               cat:"sports",      mets:{low:4.0,moderate:5.0,high:6.0,"very high":7.0}},
  {name:"Badminton",             cat:"sports",      mets:{low:4.5,moderate:5.5,high:7.0,"very high":9.0}},
  {name:"Basketball",            cat:"sports",      mets:{low:5.0,moderate:6.5,high:8.0,"very high":10.0}},
];

const container = document.getElementById('rows');

exercises.forEach((ex, i) => {
  const row = document.createElement('div');
  row.className = 'ex-row';
  row.dataset.cat = ex.cat;
  row.dataset.i = i;

  row.innerHTML = `
    <input type="checkbox" id="cb${i}">
    <label class="ex-label" for="cb${i}">${ex.name}</label>
    <select>
      <option value="" disabled selected>intensity</option>
      <option value="low">low</option>
      <option value="moderate">moderate</option>
      <option value="high">high</option>
      <option value="very high">very high</option>
    </select>
    <input type="number" placeholder="min" min="0" max="999">
    <span class="ex-unit">min</span>
    <span class="ex-kcal">— kcal</span>
  `;

  container.appendChild(row);

  const cb  = row.querySelector('input[type=checkbox]');
  const sel = row.querySelector('select');
  const num = row.querySelector('input[type=number]');
  const kcalEl = row.querySelector('.ex-kcal');

  const calc = () => {
    row.classList.toggle('active', cb.checked);
    const w   = parseFloat(document.getElementById('weight').value) || 70;
    const met = ex.mets[sel.value];
    const t   = parseFloat(num.value);
    if (cb.checked && met && t > 0) {
      const c = Math.round(met * 3.5 * w / 200 * t);
      kcalEl.textContent = c + ' kcal';
      kcalEl.classList.add('has-value');
    } else {
      kcalEl.textContent = cb.checked ? '— kcal' : '';
      kcalEl.classList.remove('has-value');
    }
    updateTotal();
  };

  cb.addEventListener('change', calc);
  sel.addEventListener('change', calc);
  num.addEventListener('input', calc);
});

document.getElementById('weight').addEventListener('input', () => {
  document.querySelectorAll('.ex-row').forEach(row => {
    const i   = +row.dataset.i;
    const cb  = row.querySelector('input[type=checkbox]');
    const sel = row.querySelector('select');
    const num = row.querySelector('input[type=number]');
    const kcalEl = row.querySelector('.ex-kcal');
    const w   = parseFloat(document.getElementById('weight').value) || 70;
    const met = exercises[i].mets[sel.value];
    const t   = parseFloat(num.value);
    if (cb.checked && met && t > 0) {
      kcalEl.textContent = Math.round(met * 3.5 * w / 200 * t) + ' kcal';
      kcalEl.classList.add('has-value');
    }
  });
  updateTotal();
});

function updateTotal() {
  let sum = 0;
  const active = [];
  const w = parseFloat(document.getElementById('weight').value) || 70;
  document.querySelectorAll('.ex-row').forEach(row => {
    const i   = +row.dataset.i;
    const cb  = row.querySelector('input[type=checkbox]');
    const sel = row.querySelector('select');
    const num = row.querySelector('input[type=number]');
    const t   = parseFloat(num.value);
    const met = exercises[i].mets[sel.value];
    if (cb.checked && met && t > 0) {
      const c = Math.round(met * 3.5 * w / 200 * t);
      sum += c;
      active.push(exercises[i].name.split(' ')[0]);
    }
  });
  document.getElementById('total-kcal').textContent = Math.round(sum) + ' kcal';
  document.getElementById('breakdown').textContent =
    active.length ? active.join(' · ') : 'no exercises selected';
}

document.querySelectorAll('.cat-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const cat = btn.dataset.cat;
    document.querySelectorAll('.ex-row').forEach(row => {
      row.classList.toggle('hidden', cat !== 'all' && row.dataset.cat !== cat);
    });
  });
});

document.getElementById('reset-btn').addEventListener('click', () => {
  document.querySelectorAll('.ex-row').forEach(row => {
    row.querySelector('input[type=checkbox]').checked = false;
    row.querySelector('select').selectedIndex = 0;
    row.querySelector('input[type=number]').value = '';
    row.querySelector('.ex-kcal').textContent = '';
    row.querySelector('.ex-kcal').classList.remove('has-value');
    row.classList.remove('active');
  });
  updateTotal();
});
</script>
</body>


</html>