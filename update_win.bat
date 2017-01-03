powershell.exe -command "if( -not (Test-Path '.\scripts') ){ New-Item -ItemType directory -Path '.\scripts'; (New-Object Net.WebClient).DownloadFile('https://raw.githubusercontent.com/prafiny/db-project/master/scripts/update.ps1','.\scripts\update.ps1'); }"

powershell.exe "Get-Content .\scripts\update.ps1 | powershell.exe -noprofile -"
