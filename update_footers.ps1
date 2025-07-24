Get-ChildItem -Path . -Recurse -Filter *.php | ForEach-Object {
    $path = $_.FullName
    $content = Get-Content $path -Raw
    if ($content -match '<footer>') {
        $newContent = $content -replace '(<p>&copy; .*? All rights reserved.</p>)', '$1 | <a href="privacy_policy.php" style="text-decoration: none; color: inherit;">Privacy Policy</a>'
        Set-Content $path $newContent -NoNewline
    }
}