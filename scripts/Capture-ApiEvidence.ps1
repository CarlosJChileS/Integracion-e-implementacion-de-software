param(
    [string] $BaseUrl = 'http://127.0.0.1:8000',
    [string] $OutputPath = 'evidencias/http-results.json'
)

$ErrorActionPreference = 'Stop'

if ([string]::IsNullOrWhiteSpace($env:ILIA_DEMO_TOKEN)) {
    throw 'Define ILIA_DEMO_TOKEN con un token ficticio de Sanctum antes de ejecutar este script.'
}

$client = [System.Net.Http.HttpClient]::new()
$client.DefaultRequestHeaders.Authorization = [System.Net.Http.Headers.AuthenticationHeaderValue]::new('Bearer', $env:ILIA_DEMO_TOKEN)
$client.DefaultRequestHeaders.Accept.Add([System.Net.Http.Headers.MediaTypeWithQualityHeaderValue]::new('application/json'))

$cases = @(
    @{ name = 'P1'; json = '{"course_slug":"prompting-para-docentes","cohort_code":"ILIA-DEMO"}' },
    @{ name = 'P2'; json = '{"cohort_code":"ILIA-DEMO"}' },
    @{ name = 'P3'; json = '{"course_slug":42}' }
)

$results = foreach ($case in $cases) {
    $content = [System.Net.Http.StringContent]::new($case.json, [System.Text.Encoding]::UTF8, 'application/json')
    $response = $client.PostAsync("$BaseUrl/api/enrollments", $content).GetAwaiter().GetResult()
    $responseText = $response.Content.ReadAsStringAsync().GetAwaiter().GetResult()

    @{
        case = $case.name
        request = ConvertFrom-Json $case.json -AsHashtable
        status = [int] $response.StatusCode
        response = ConvertFrom-Json $responseText -AsHashtable
    }
}

$projectRoot = Split-Path $PSScriptRoot -Parent
$destination = Join-Path $projectRoot $OutputPath
$results | ConvertTo-Json -Depth 10 | Set-Content -Path $destination -Encoding utf8
Write-Output "Saved P1/P2/P3 HTTP responses to $destination"
