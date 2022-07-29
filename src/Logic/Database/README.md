<div align="center">
  <a href="https://github.com/BROCKHAUS-AG/contao-release-stages-bundle">
    <img src="/images/logo.svg" alt="Logo" width="120">
  </a>

<h3 align="center">Contao Release Stages Bundle</h3>

  <p align="center">
    Database migrator, deployer and rollbacker for Contao.
  <br />
    <a href="https://github.com/BROCKHAUS-AG/contao-release-stages-bundle"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="https://github.com/BROCKHAUS-AG/contao-release-stages-bundle/issues">Report Bug</a>
    ·
    <a href="https://github.com/BROCKHAUS-AG/contao-release-stages-bundle/issues">Request Feature</a>
  </p>
</div>

<h3>Database migrator</h3>
<p>
    The migrator creates a migration of the contao database. It creates on it´s own insert, update and delete
    statements to run them later after the deployment process succeeded on the database.
</p>
<p>
    <strong>Important notes</strong>
    <br />
    The migrator can´t create relations between tables. Until yet Contao don´t need them.
</p>

<h3>Database deployer</h3>
<p>
    The database deployer extract the database dump which was created from the migrator and calls simply the database
    migrator. Afterwards it runs on the prod stage the migration file via a shell script.
</p>

<h3>Database rollbacker</h3>
<p>
    The database rollbacker is a simple wrapper around the database migrator. It rolls back the last migration.
</p>

