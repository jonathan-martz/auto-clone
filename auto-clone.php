<?php
function loadConfig()
{
    $filename = __DIR__ . '/config.json';
    $file = file_get_contents($filename);
    return json_decode($file, true);
}

$config = loadConfig();
if ($config) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/users/' . $config['username'] . '/repos?per_page=100');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'jmartz-auto-clone');
    $output = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($output);
    $newData = [];

    function isOther($name)
    {
        $list = ['jmartz', 'magento2', 'lumen', 'hetzner'];

        return in_array($name, $list);
    }

    foreach ($data as $key => $repo) {

        $name = explode('-', $repo->name);

        if (isOther($name[0], $newData)) {
            if (empty($newData[$name[0]])) {
                $newData[$name[0]] = [];
            }

            $newData[$name[0]][] = [
                'name' => $repo->name,
                'url' => $repo->ssh_url
            ];
        } else {
            if (empty($newData['other'])) {
                $newData['other'] = [];
            }

            $newData['other'][] = [
                'name' => $repo->name,
                'url' => $repo->ssh_url
            ];
        }
    }

    foreach ($newData as $folder => $repos) {
        exec('mkdir -p ' . $folder);
        foreach ($repos as $key => $repo) {
            $command = 'cd ' . $folder . ' && git clone ' . $repo['url'];
            exec($command);
        }
    }
}
