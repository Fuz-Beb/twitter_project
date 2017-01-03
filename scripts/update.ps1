function Expand-ZIPFile($file, $destination)
{
$shell = new-object -com shell.application
$zip = $shell.NameSpace($file)
foreach($item in $zip.items())
{
$shell.Namespace($destination).copyhere($item)
}
}

$DOCDIR = (Get-Item -Path ".\" -Verbose).FullName

"Downloading master.zip"
"----------------------"
""
(New-Object Net.WebClient).DownloadFile('https://github.com/prafiny/db-project/archive/master.zip',"$DOCDIR\master.zip");
Expand-ZIPFile -File "$DOCDIR\master.zip" -Destination "$DOCDIR\"; Remove-Item "$DOCDIR\master.zip";
"Updating"
"--------"
""
$myArray = "autoload.php","composer.json","composer-setup.php","controller","instructions","lib","README.md","tests","view","www","scripts","update_unix.sh"

$shell = new-object -com shell.application
foreach($item in $myArray)
{
if( (Test-Path $DOCDIR\$item) ) { Remove-Item -path $DOCDIR\$item -recurse }
}

Get-Content .\db-project-master\scripts\replace.ps1 | powershell.exe -noprofile -
