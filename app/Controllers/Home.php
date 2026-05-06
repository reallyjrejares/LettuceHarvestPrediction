<?php

namespace App\Controllers;

class Home extends BaseController
{
    private function getGrowthDays(string $variety): int
    {
        $key = strtolower(trim($variety));
        $map = [
            'romaine' => 30,
            'iceberg' => 40,
            'butterhead' => 28,
            'green leaf' => 32,
            'red leaf' => 32,
            'oakleaf' => 30,
        ];

        return $map[$key] ?? 30;
    }

    private function getEnvironmentalData(): array
    {
        $session = session();
        $env = $session->get('environment') ?? [];

        return [
            'temperature_c' => $env['temperature_c'] ?? null,
            'humidity_pct' => $env['humidity_pct'] ?? null,
            'tds_ppm' => $env['tds_ppm'] ?? null,
            'ph_level' => $env['ph_level'] ?? null,
        ];
    }

    public function weather()
    {
        $session = session();
        $isLoggedIn = $session->get('is_logged_in') === true;
        $hasUser = (bool) $session->get('user_id');

        if (! $isLoggedIn || ! $hasUser) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['ok' => false, 'error' => 'Unauthorized.']);
        }

        $payload = $this->request->getJSON(true);
        if (! is_array($payload) || $payload === []) {
            $payload = $this->request->getPost();
        }

        $lat = $payload['lat'] ?? null;
        $lon = $payload['lon'] ?? null;
        $city = $payload['city'] ?? null;

        $query = null;
        if ($city !== null && is_string($city) && trim($city) !== '') {
            $query = trim($city);
        } elseif (is_numeric($lat) && is_numeric($lon)) {
            $lat = (float) $lat;
            $lon = (float) $lon;

            if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
                return $this->response
                    ->setStatusCode(422)
                    ->setJSON(['ok' => false, 'error' => 'Latitude/longitude are out of range.']);
            }

            $query = $lat . ',' . $lon;
        } else {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['ok' => false, 'error' => 'Location is required.']);
        }

        $apiKey = getenv('WEATHERAPI_KEY') ?: ($_ENV['WEATHERAPI_KEY'] ?? null);
        if (! $apiKey) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['ok' => false, 'error' => 'Weather API key is not configured.']);
        }

        $client = \Config\Services::curlrequest();

        try {
            $response = $client->get('https://api.weatherapi.com/v1/current.json', [
                'query' => [
                    'key' => $apiKey,
                    'q' => $query,
                    'aqi' => 'no',
                ],
                'http_errors' => false,
            ]);
        } catch (\Throwable $e) {
            return $this->response
                ->setStatusCode(502)
                ->setJSON(['ok' => false, 'error' => 'Failed to reach weather service.']);
        }

        $status = $response->getStatusCode();
        $data = json_decode($response->getBody(), true);

        if ($status !== 200 || ! is_array($data) || isset($data['error'])) {
            return $this->response
                ->setStatusCode(502)
                ->setJSON(['ok' => false, 'error' => 'Weather service returned an error.']);
        }

        $location = $data['location']['name'] ?? null;
        $region = $data['location']['region'] ?? null;
        $country = $data['location']['country'] ?? null;
        $parts = array_values(array_filter([$location, $region, $country], static function ($value) {
            return $value !== null && $value !== '';
        }));
        if ($parts) {
            $normalized = [];
            $unique = [];
            foreach ($parts as $part) {
                $key = strtolower($part);
                if (! isset($normalized[$key])) {
                    $normalized[$key] = true;
                    $unique[] = $part;
                }
            }
            $parts = $unique;
        }
        $locationText = $parts ? implode(', ', $parts) : 'Current Location';

        return $this->response->setJSON([
            'ok' => true,
            'location' => $locationText,
            'temperature_c' => $data['current']['temp_c'] ?? null,
            'humidity_pct' => $data['current']['humidity'] ?? null,
            'condition' => $data['current']['condition']['text'] ?? null,
        ]);
    }

    public function updateEnvironment()
    {
        $session = session();
        $isLoggedIn = $session->get('is_logged_in') === true;
        $hasUser = (bool) $session->get('user_id');

        if (! $isLoggedIn || ! $hasUser) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['ok' => false, 'error' => 'Unauthorized.']);
        }

        $payload = $this->request->getJSON(true);
        if (! is_array($payload) || $payload === []) {
            $payload = $this->request->getPost();
        }

        $tds = $payload['tds_ppm'] ?? null;
        $ph = $payload['ph_level'] ?? null;

        if ($tds !== null && $tds !== '' && ! is_numeric($tds)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['ok' => false, 'error' => 'TDS must be a number.']);
        }

        if ($ph !== null && $ph !== '' && ! is_numeric($ph)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['ok' => false, 'error' => 'pH must be a number.']);
        }

        $env = $session->get('environment') ?? [];
        if ($tds !== null && $tds !== '') {
            $env['tds_ppm'] = (float) $tds;
        }
        if ($ph !== null && $ph !== '') {
            $env['ph_level'] = (float) $ph;
        }

        $session->set('environment', $env);

        return $this->response->setJSON([
            'ok' => true,
            'tds_ppm' => $env['tds_ppm'] ?? null,
            'ph_level' => $env['ph_level'] ?? null,
        ]);
    }

    public function index(): string
    {
        helper('form');
        return view('login');
    }

    public function adminLogin()
    {
        $session = session();
        $isAdminLoggedIn = $session->get('admin_logged_in') === true;
        $hasAdmin = (bool) $session->get('admin_id');

        if ($isAdminLoggedIn && $hasAdmin) {
            return redirect()->to(site_url('admin'));
        }

        $isUserLoggedIn = $session->get('is_logged_in') === true;
        $hasUser = (bool) $session->get('user_id');
        if ($isUserLoggedIn && $hasUser) {
            return redirect()->to(site_url('dashboard'));
        }

        helper('form');
        return view('admin_login');
    }

    public function adminLoginPost()
    {
        helper(['form']);

        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to(site_url('admin/login'))
                ->withInput()
                ->with('adminErrors', $this->validator->getErrors());
        }

        $adminModel = new \App\Models\AdminModel();
        $admin = $adminModel->where('username', $this->request->getPost('username'))->first();

        if (! $admin || ! password_verify($this->request->getPost('password'), $admin['password_hash'])) {
            return redirect()->to(site_url('admin/login'))
                ->withInput()
                ->with('adminErrors', ['login' => 'Invalid username or password.']);
        }

        $session = session();
        $session->remove(['user_id', 'username', 'name', 'email', 'is_logged_in']);

        $session->set([
            'admin_id' => (int) $admin['id'],
            'admin_username' => (string) ($admin['username'] ?? ''),
            'admin_logged_in' => true,
        ]);

        return redirect()->to(site_url('admin'));
    }

    public function dashboard(): string
    {
        $session = session();
        $isLoggedIn = $session->get('is_logged_in') === true;
        $hasUser = (bool) $session->get('user_id');

        if (! $isLoggedIn || ! $hasUser) {
            return redirect()->to(site_url('/'))->with('errors', ['login' => 'Please sign in to continue.']);
        }
        $plantModel = new \App\Models\PlantModel();
        $plants = $plantModel
            ->where('user_id', (int) $session->get('user_id'))
            ->orderBy('date_planted', 'DESC')
            ->findAll();

        return view('dashboard', [
            'plants' => $plants,
            'activePage' => 'dashboard',
            'title' => 'Dashboard',
        ]);
    }

    public function records(): string
    {
        $session = session();
        $isLoggedIn = $session->get('is_logged_in') === true;
        $hasUser = (bool) $session->get('user_id');

        if (! $isLoggedIn || ! $hasUser) {
            return redirect()->to(site_url('/'))->with('errors', ['login' => 'Please sign in to continue.']);
        }

        $plantModel = new \App\Models\PlantModel();
        $plants = $plantModel
            ->where('user_id', (int) $session->get('user_id'))
            ->orderBy('date_planted', 'DESC')
            ->findAll();

        return view('records', [
            'plants' => $plants,
            'activePage' => 'records',
            'title' => 'Lettuce Records',
        ]);
    }

    public function predictions(): string
    {
        $session = session();
        $isLoggedIn = $session->get('is_logged_in') === true;
        $hasUser = (bool) $session->get('user_id');

        if (! $isLoggedIn || ! $hasUser) {
            return redirect()->to(site_url('/'))->with('errors', ['login' => 'Please sign in to continue.']);
        }

        $plantModel = new \App\Models\PlantModel();
        $plants = $plantModel
            ->where('user_id', (int) $session->get('user_id'))
            ->orderBy('date_planted', 'DESC')
            ->findAll();

        return view('predictions', [
            'plants' => $plants,
            'activePage' => 'predictions',
            'title' => 'Predictions',
        ]);
    }

    public function analytics(): string
    {
        $session = session();
        $isLoggedIn = $session->get('is_logged_in') === true;
        $hasUser = (bool) $session->get('user_id');

        if (! $isLoggedIn || ! $hasUser) {
            return redirect()->to(site_url('/'))->with('errors', ['login' => 'Please sign in to continue.']);
        }

        $userId = (int) $session->get('user_id');
        $plantModel = new \App\Models\PlantModel();
        $plants = $plantModel->where('user_id', $userId)->findAll();

        $totalBatches = count($plants);
        $totalGrowthDays = 0;
        $harvestedCount = 0;
        $varietyCounts = [];

        $today = new \DateTimeImmutable('today');

        foreach ($plants as $plant) {
            $growthDays = $this->getGrowthDays($plant['variety']);
            $totalGrowthDays += $growthDays;

            $varietyRaw = trim((string) ($plant['variety'] ?? 'Unknown'));
            $varietyLabel = $varietyRaw !== '' ? $varietyRaw : 'Unknown';
            if (! isset($varietyCounts[$varietyLabel])) {
                $varietyCounts[$varietyLabel] = 0;
            }
            $varietyCounts[$varietyLabel]++;

            $predictedHarvest = \DateTimeImmutable::createFromFormat('Y-m-d', (string) ($plant['predicted_harvest'] ?? ''));
            if ($predictedHarvest && $predictedHarvest <= $today) {
                $harvestedCount++;
            }
        }

        $avgGrowthDays = $totalBatches > 0 ? round($totalGrowthDays / $totalBatches, 1) : 0;
        $harvestSuccess = $totalBatches > 0 ? round(($harvestedCount / $totalBatches) * 100, 1) : 0;

        arsort($varietyCounts);
        $topVarieties = array_slice($varietyCounts, 0, 5, true);

        return view('analytics', [
            'activePage' => 'analytics',
            'title' => 'Analytics',
            'totalBatches' => $totalBatches,
            'avgGrowthDays' => $avgGrowthDays,
            'harvestSuccess' => $harvestSuccess,
            'topVarieties' => $topVarieties,
            'plants' => $plants,
        ]);
    }

    public function adminDashboard(): string
    {
        $userModel = new \App\Models\UserModel();
        $adminModel = new \App\Models\AdminModel();
        $plantModel = new \App\Models\PlantModel();

        $users = $userModel->orderBy('id', 'ASC')->findAll();
        $plants = $plantModel->orderBy('date_planted', 'ASC')->findAll();

        $totalUsers = count($users);
        $adminCount = $adminModel->countAllResults();
        $regularCount = $totalUsers;

        $totalPlants = count($plants);
        $plantsByUser = [];
        $varietyCounts = [];

        $monthLabels = [];
        $monthCounts = [];
        $monthLookup = [];
        $monthBase = new \DateTimeImmutable('first day of this month');
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = $monthBase->modify('-' . $i . ' months');
            $monthKey = $monthDate->format('Y-m');
            $monthLabels[] = $monthDate->format('M Y');
            $monthCounts[] = 0;
            $monthLookup[$monthKey] = count($monthCounts) - 1;
        }

        $today = new \DateTimeImmutable('today');
        $weekAhead = $today->modify('+7 days');
        $upcomingHarvests = 0;
        $overdueHarvests = 0;

        foreach ($plants as $plant) {
            $userId = (int) ($plant['user_id'] ?? 0);
            if ($userId > 0) {
                if (! isset($plantsByUser[$userId])) {
                    $plantsByUser[$userId] = 0;
                }
                $plantsByUser[$userId]++;
            }

            $varietyRaw = trim((string) ($plant['variety'] ?? 'Unknown'));
            $varietyLabel = $varietyRaw !== '' ? $varietyRaw : 'Unknown';
            if (! isset($varietyCounts[$varietyLabel])) {
                $varietyCounts[$varietyLabel] = 0;
            }
            $varietyCounts[$varietyLabel]++;

            $plantDate = \DateTimeImmutable::createFromFormat('Y-m-d', (string) ($plant['date_planted'] ?? ''));
            if ($plantDate) {
                $monthKey = $plantDate->format('Y-m');
                if (isset($monthLookup[$monthKey])) {
                    $monthCounts[$monthLookup[$monthKey]]++;
                }
            }

            $predictedHarvest = \DateTimeImmutable::createFromFormat('Y-m-d', (string) ($plant['predicted_harvest'] ?? ''));
            if ($predictedHarvest) {
                if ($predictedHarvest < $today) {
                    $overdueHarvests++;
                } elseif ($predictedHarvest <= $weekAhead) {
                    $upcomingHarvests++;
                }
            }
        }

        $usersWithPlants = count($plantsByUser);
        $averagePlantsPerUser = $totalUsers > 0 ? round($totalPlants / $totalUsers, 2) : 0.0;

        arsort($varietyCounts);
        $topVarieties = array_slice($varietyCounts, 0, 6, true);
        $varietyLabels = array_values(array_map('strval', array_keys($topVarieties)));
        $varietySeries = array_values(array_map('intval', array_values($topVarieties)));

        $usersById = [];
        foreach ($users as $user) {
            $usersById[(int) $user['id']] = $user;
        }

        $topGrower = null;
        if ($plantsByUser !== []) {
            arsort($plantsByUser);
            $topUserId = (int) array_key_first($plantsByUser);
            if ($topUserId > 0 && isset($usersById[$topUserId])) {
                $topUser = $usersById[$topUserId];
                $topGrower = [
                    'name' => (string) ($topUser['name'] ?: $topUser['username']),
                    'username' => (string) ($topUser['username'] ?? ''),
                    'plants' => (int) $plantsByUser[$topUserId],
                ];
            }
        }

        $recentUsers = $userModel->orderBy('created_at', 'DESC')->findAll(6);
        $recentPlants = $plantModel
            ->select('plants.id, plants.variety, plants.date_planted, plants.predicted_harvest, plants.user_id, users.username')
            ->join('users', 'users.id = plants.user_id', 'left')
            ->orderBy('plants.created_at', 'DESC')
            ->findAll(8);

        return view('admin_dashboard', [
            'totalUsers' => $totalUsers,
            'adminCount' => $adminCount,
            'regularCount' => $regularCount,
            'totalPlants' => $totalPlants,
            'usersWithPlants' => $usersWithPlants,
            'averagePlantsPerUser' => $averagePlantsPerUser,
            'upcomingHarvests' => $upcomingHarvests,
            'overdueHarvests' => $overdueHarvests,
            'topGrower' => $topGrower,
            'monthLabels' => $monthLabels,
            'monthSeries' => $monthCounts,
            'varietyLabels' => $varietyLabels,
            'varietySeries' => $varietySeries,
            'recentUsers' => $recentUsers,
            'recentPlants' => $recentPlants,
            'activePage' => 'admin-dashboard',
            'title' => 'Admin Dashboard',
        ]);
    }

    public function adminUsers(): string
    {
        $userModel = new \App\Models\UserModel();
        $adminModel = new \App\Models\AdminModel();
        $users = $userModel->orderBy('id', 'ASC')->findAll();
        $adminCount = $adminModel->countAllResults();

        $plantModel = new \App\Models\PlantModel();
        $plantRows = $plantModel
            ->select('user_id, COUNT(*) AS plant_total')
            ->groupBy('user_id')
            ->findAll();

        $plantCounts = [];
        foreach ($plantRows as $row) {
            $plantCounts[(int) $row['user_id']] = (int) ($row['plant_total'] ?? 0);
        }

        return view('admin_users', [
            'users' => $users,
            'plantCounts' => $plantCounts,
            'adminCount' => $adminCount,
            'activePage' => 'admin-users',
            'title' => 'Admin Users',
        ]);
    }

    public function deleteUser(int $id)
    {
        $id = (int) $id;

        if ($id <= 0) {
            return redirect()->to(site_url('admin/users'))->with('adminError', 'Invalid user ID.');
        }

        $userModel = new \App\Models\UserModel();
        $target = $userModel->find($id);
        if (! $target) {
            return redirect()->to(site_url('admin/users'))->with('adminError', 'User not found.');
        }

        $plantModel = new \App\Models\PlantModel();
        $plantModel->where('user_id', $id)->delete();

        $deleted = $userModel->delete($id);
        if (! $deleted) {
            return redirect()->to(site_url('admin/users'))->with('adminError', 'Failed to delete user.');
        }

        return redirect()->to(site_url('admin/users'))->with('adminSuccess', 'User and related records were deleted.');
    }


    public function addPlant()
    {
        $session = session();
        $isLoggedIn = $session->get('is_logged_in') === true;
        $hasUser = (bool) $session->get('user_id');

        if (! $isLoggedIn || ! $hasUser) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['ok' => false, 'error' => 'Unauthorized.']);
        }

        helper(['form']);

        $rules = [
            'variety' => 'required|min_length[2]|max_length[120]',
            'date_planted' => 'required|valid_date[Y-m-d]',
        ];

        if (! $this->validate($rules)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['ok' => false, 'errors' => $this->validator->getErrors()]);
        }

        $plantDate = $this->request->getPost('date_planted');
        $variety = $this->request->getPost('variety');
        $growthDays = $this->getGrowthDays($variety);
        $date = new \DateTime($plantDate);
        $date->modify('+' . $growthDays . ' days');
        $predictedHarvest = $date->format('Y-m-d');
        $env = $this->getEnvironmentalData();

        $plantModel = new \App\Models\PlantModel();
        $insertId = $plantModel->insert([
            'user_id' => (int) $session->get('user_id'),
            'variety' => $variety,
            'date_planted' => $plantDate,
            'predicted_harvest' => $predictedHarvest,
            'temperature_c' => $env['temperature_c'],
            'humidity_pct' => $env['humidity_pct'],
            'tds_ppm' => $env['tds_ppm'],
            'ph_level' => $env['ph_level'],
        ], true);

        if (! $insertId) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['ok' => false, 'error' => 'Failed to save plant.']);
        }

        return $this->response->setJSON([
            'ok' => true,
            'plant' => [
                'id' => $insertId,
                'variety' => $variety,
                'date_planted' => $plantDate,
                'predicted_harvest' => $predictedHarvest,
            ],
        ]);
    }

    public function updatePlant(int $id)
    {
        $session = session();
        $isLoggedIn = $session->get('is_logged_in') === true;
        $hasUser = (bool) $session->get('user_id');

        if (! $isLoggedIn || ! $hasUser) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['ok' => false, 'error' => 'Unauthorized.']);
        }

        helper(['form']);

        $rules = [
            'variety' => 'required|min_length[2]|max_length[120]',
            'date_planted' => 'required|valid_date[Y-m-d]',
        ];

        if (! $this->validate($rules)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['ok' => false, 'errors' => $this->validator->getErrors()]);
        }

        $plantModel = new \App\Models\PlantModel();
        $plant = $plantModel->find($id);

        if (! $plant || (int) $plant['user_id'] !== (int) $session->get('user_id')) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['ok' => false, 'error' => 'Plant not found.']);
        }

        $plantDate = $this->request->getPost('date_planted');
        $variety = $this->request->getPost('variety');
        $growthDays = $this->getGrowthDays($variety);
        $date = new \DateTime($plantDate);
        $date->modify('+' . $growthDays . ' days');
        $predictedHarvest = $date->format('Y-m-d');
        $env = $this->getEnvironmentalData();

        $updated = $plantModel->update($id, [
            'variety' => $variety,
            'date_planted' => $plantDate,
            'predicted_harvest' => $predictedHarvest,
            'temperature_c' => $env['temperature_c'],
            'humidity_pct' => $env['humidity_pct'],
            'tds_ppm' => $env['tds_ppm'],
            'ph_level' => $env['ph_level'],
        ]);

        if (! $updated) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['ok' => false, 'error' => 'Failed to update plant.']);
        }

        return $this->response->setJSON([
            'ok' => true,
            'plant' => [
                'id' => $id,
                'variety' => $variety,
                'date_planted' => $plantDate,
                'predicted_harvest' => $predictedHarvest,
            ],
        ]);
    }

    public function updatePredictedHarvest(int $id)
    {
        $session = session();
        $isLoggedIn = $session->get('is_logged_in') === true;
        $hasUser = (bool) $session->get('user_id');

        if (! $isLoggedIn || ! $hasUser) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['ok' => false, 'error' => 'Unauthorized.']);
        }

        $payload = $this->request->getJSON(true);
        if (! is_array($payload) || $payload === []) {
            $payload = $this->request->getPost();
        }

        $predictedHarvest = $payload['predicted_harvest'] ?? null;
        if (! is_string($predictedHarvest) || $predictedHarvest === '') {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['ok' => false, 'error' => 'Predicted harvest date is required.']);
        }

        $date = \DateTime::createFromFormat('Y-m-d', $predictedHarvest);
        if (! $date || $date->format('Y-m-d') !== $predictedHarvest) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['ok' => false, 'error' => 'Invalid harvest date.']);
        }

        $plantModel = new \App\Models\PlantModel();
        $plant = $plantModel->find($id);

        if (! $plant || (int) $plant['user_id'] !== (int) $session->get('user_id')) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['ok' => false, 'error' => 'Plant not found.']);
        }

        $updated = $plantModel->update($id, [
            'predicted_harvest' => $predictedHarvest,
        ]);

        if (! $updated) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['ok' => false, 'error' => 'Failed to update harvest date.']);
        }

        return $this->response->setJSON([
            'ok' => true,
            'predicted_harvest' => $predictedHarvest,
        ]);
    }

    public function deletePlant(int $id)
    {
        $session = session();
        $isLoggedIn = $session->get('is_logged_in') === true;
        $hasUser = (bool) $session->get('user_id');

        if (! $isLoggedIn || ! $hasUser) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['ok' => false, 'error' => 'Unauthorized.']);
        }

        $plantModel = new \App\Models\PlantModel();
        $plant = $plantModel->find($id);

        if (! $plant || (int) $plant['user_id'] !== (int) $session->get('user_id')) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['ok' => false, 'error' => 'Plant not found.']);
        }

        $deleted = $plantModel->delete($id);

        if (! $deleted) {
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['ok' => false, 'error' => 'Failed to delete plant.']);
        }

        return $this->response->setJSON(['ok' => true]);
    }

    public function register()
    {
        helper(['form']);

        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'email' => 'required|valid_email|max_length[150]|is_unique[users.email]',
            'username' => 'required|min_length[3]|max_length[60]|alpha_numeric|is_unique[users.username]',
            'password' => 'required|min_length[8]|max_length[72]|regex_match[/[A-Z]/]|regex_match[/[0-9]/]|regex_match[/[^A-Za-z0-9]/]',
            'confirm_password' => 'required|matches[password]',
        ];

        $messages = [
            'password' => [
                'regex_match' => 'Password must include an uppercase letter, a number, and a symbol.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->to(site_url('/'))
                ->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('showRegister', true);
        }

        $userModel = new \App\Models\UserModel();
        try {
            $result = $userModel->insert([
                'name' => $this->request->getPost('name'),
                'email' => $this->request->getPost('email'),
                'username' => $this->request->getPost('username'),
                'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            ]);
        } catch (\Throwable $e) {
            return redirect()->to(site_url('/'))
                ->withInput()
                ->with('errors', ['db' => 'DB ERROR: ' . $e->getMessage()])
                ->with('showRegister', true);
        }

        if ($result === false) {
            $errors = $userModel->errors();
            return redirect()->to(site_url('/'))
                ->withInput()
                ->with('errors', $errors ?: ['db' => 'Registration failed. Please try again.'])
                ->with('showRegister', true);
        }

        return redirect()->to(site_url('/'))->with('success', 'Registration successful. You can sign in now.');
    }

    public function loginPost()
    {
        helper(['form']);

        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to(site_url('/'))
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('username', $this->request->getPost('username'))->first();

        if (! $user || ! password_verify($this->request->getPost('password'), $user['password_hash'])) {
            return redirect()->to(site_url('/'))
                ->withInput()
                ->with('errors', ['login' => 'Invalid username or password.']);
        }

        $session = session();
        $session->remove(['admin_id', 'admin_username', 'admin_logged_in']);

        $session->set([
            'user_id' => $user['id'],
            'username' => $user['username'],
            'name' => $user['name'] ?? null,
            'email' => $user['email'] ?? null,
            'is_logged_in' => true,
        ]);

        return redirect()->to(site_url('dashboard'));
    }

    public function logout()
    {
        $session = session();
        $session->remove(['user_id', 'username', 'name', 'email', 'is_logged_in', 'admin_id', 'admin_username', 'admin_logged_in']);
        $session->setFlashdata('success', 'Success, you have been logged out.');
        $session->regenerate(true);
        return redirect()->to(site_url('/'));
    }
}
