# Get all files recursively, excluding the .git directory
$files = Get-ChildItem -File -Recurse | Where-Object { $_.FullName -notmatch "\\\.git\\" }

foreach ($file in $files) {
    # Get the relative path and handle special characters/spaces by quoting
    $relativePath = Resolve-Path -Path $file.FullName -Relative
    
    Write-Host "------------------------------------------------" -ForegroundColor White
    Write-Host "Syncing file: $relativePath" -ForegroundColor Cyan
    
    # 1. Add the specific file
    git add "$relativePath"
    
    # 2. Commit with the filename as the message
    git commit -m "Add $relativePath"
    
    # 3. Push immediately
    git push
}

Write-Host "------------------------------------------------" -ForegroundColor Green
Write-Host "Workflow complete." -ForegroundColor Green