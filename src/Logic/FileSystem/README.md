<div align="center">
  <a href="https://github.com/BROCKHAUS-AG/contao-release-stages-bundle">
    <img src="/images/logo.svg" alt="Logo" width="120">
  </a>

<h3 align="center">Contao Release Stages Bundle</h3>

  <p align="center">
    File system migrator, deployer and rollbacker for Contao.
  <br />
    <a href="https://github.com/BROCKHAUS-AG/contao-release-stages-bundle"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="https://github.com/BROCKHAUS-AG/contao-release-stages-bundle/issues">Report Bug</a>
    ·
    <a href="https://github.com/BROCKHAUS-AG/contao-release-stages-bundle/issues">Request Feature</a>
  </p>
</div>

<h3>File system migrator</h3>
<p>
    The migrator creates a migration/archive of the "files/content" directory to extract the files later on the prod
    stage.
</p>
<p>
    <strong>Important notes</strong>
    <br />
    The migrator can´t create relations between tables. Until yet Contao don´t need them.
</p>

<h3>File system deployer</h3>
<p>
    The file system deployer extract the file which were archived on the prod stage and deploy them to the
    "files/content" directory.
</p>

<h3>File system rollbacker</h3>
<p>
    The file system rollbacker is a simple wrapper around the file system migrator. It rolls back the last migration.
</p>

