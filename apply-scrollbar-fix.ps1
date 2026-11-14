# Safer alternative to replacing theme.css: appends the fix only if it is not there yet.
# Run from the project folder:  powershell -ExecutionPolicy Bypass -File .\apply-scrollbar-fix.ps1
$f = "resources\css\theme.css"
if (-not (Test-Path $f)) { Write-Host "Run this inside the casa-verde project folder."; exit 1 }
if (Select-String -Path $f -Pattern "scrollbar-gutter" -Quiet) { Write-Host "Already applied."; exit 0 }
Add-Content $f "`n/* Reserve the scrollbar space on every page so the header and content never jump between short and long pages. */`nhtml { scrollbar-gutter: stable; }`n@supports not (scrollbar-gutter: stable) { html { overflow-y: scroll; } }"
Write-Host "Done. Now run: npm run build"
