$DOCDIR = (Get-Item -Path ".\" -Verbose).FullName
$myArray = "autoload.php","composer.json","composer-setup.php","controller","instructions","lib","README.md","tests","view","www","scripts","update_unix.sh"
$shell = new-object -com shell.application
foreach($item in $myArray)
{
$shell.Namespace($DOCDIR).copyhere("$DOCDIR\db-project-master\$item")
}
Remove-Item -path $DOCDIR\db-project-master -recurse

"Updating composer"
"-----------------"
""
php composer-setup.php --install-dir=. --filename=composer

"Updating composer packages"
"--------------------------"
""
php composer update

Write-Host -NoNewLine 'Press any key to continue...';
$null = $Host.UI.RawUI.ReadKey('NoEcho,IncludeKeyDown');

