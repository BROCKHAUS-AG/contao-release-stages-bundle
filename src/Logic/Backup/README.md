<div align="center">
  <a href="https://github.com/BROCKHAUS-AG/contao-release-stages-bundle">
    <img src="/images/logo.svg" alt="Logo" width="120">
  </a>

<h3 align="center">Contao Release Stages Bundle</h3>

  <p align="center">
    Backup creator for Contao.
  <br />
    <a href="https://github.com/BROCKHAUS-AG/contao-release-stages-bundle"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="https://github.com/BROCKHAUS-AG/contao-release-stages-bundle/issues">Report Bug</a>
    ·
    <a href="https://github.com/BROCKHAUS-AG/contao-release-stages-bundle/issues">Request Feature</a>
  </p>
</div>

<h3>What does the backup creator</h3>
<p>
This class creates a backup of the Contao database with all tables which have the filename pattern  "tl_*".
<br>
Also, the files in the upload directory ("files/content") are backed up.
</p>

<h3>Where are the backups placed</h3>
<p>
    You can find all backups in the "backup" directory. Each backup is named with the current timestamp and is placed
    in different directories ("database" and "file_system").
</p>
