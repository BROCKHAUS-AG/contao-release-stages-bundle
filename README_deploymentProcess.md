<div align="center">
  <a href="https://github.com/BROCKHAUS-AG/contao-release-stages-bundle">
    <img src="/images/logo.svg" alt="Logo" width="120">
  </a>

<h3 align="center">Contao Release Stages Bundle</h3>

  <p align="center">
    Deployment process for Contao releases.
  <br />
    <a href="https://github.com/BROCKHAUS-AG/contao-release-stages-bundle"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="https://github.com/BROCKHAUS-AG/contao-release-stages-bundle/issues">Report Bug</a>
    ·
    <a href="https://github.com/BROCKHAUS-AG/contao-release-stages-bundle/issues">Request Feature</a>
  </p>
</div>

<h3>Workflow</h3>
Before we can start with deploy a new release, we have to check if there is any active deployment process running.
<br />
After we have checked it we can proceed with the deployment process.
<br />
The deployment process is divided into six steps:
1. Creating a version number
2. Upload Script files
3. Create backups
4. Create and upload a new release
5. Deploy the new release
6. Give response

<h4>Step 1: Creating a version number</h4>
Let´s start hat the first one, creating a version number. I think that is the easiest step ;) <br />
We now that the version number is only for the marketing people who maybe want to know what is the current version of
the project. <br />
So we can create a version number like this: <br />
1. Get the latest version number from the database
   1. If the version number is available in the database, we can increment it by one.
   2. If the version number is not available in the database, we can create a dummy version and set it to 1.1.
2. After that we can upload the new version number

<h4>Step 2: Upload Script files</h4>
Continue with step two, upload script files to the prod stage. <br />
We developed different script files to execute the deployment process without have a constant connection to the prod
stage via. ftp or ssh. <br />
If the files aren`t available the synchronizer will upload them to the prod stage. <br />
The files are stored in the following directory: "contao/scripts" <br />

<h4>Step 3: Create backups</h4>
After we have uploaded the script files, we are able to create backups from the database and the file system. <br />
This could be done by triggering two script files in the "scripts/backup" directory "backup_database.sh" and
"backup_file_system.sh".
The "backup_database.sh" file creates a mysqldump of the contao database. <br />
The "backup_file_system.sh" file creates an archive of the "files/backups" directory. <br />
The backups are stored in the following directory: "contao/backups". The name of each backup is the actual timestamp
when the backup was created. These backups were stored in different directories "database" and "file_system" <br />

<h4>Step 4: Create and upload a new release</h4>
When the backups were created we can proceed with the main process, create and upload the new release. <br />
Now we have to build und upload the migration file for the database and the file system, how this works is described
in other documentations. You can find the documentation for the database [here](/src/Logic/Database/README.md) and for
the file system [here](/src/Logic/FileSystem/README.md). <br />

<h4>Step 5: Deploy the new release</h4>
Now we are able to deploy the new release. We easily can do this by triggering the script file "migrate_database.sh".
to migrate the database and "un_archive.sh" to upload the file system. <br />

<h4>Step 6: Give response</h4>
After the deployment process is finished, we can give a response to the user and save the state to the database. <br />


<h3>Error handling</h3>
If an error throw in the build and upload section, the deployment process will be aborted. <br />
If an error throw in the migration section, the deployment process will be aborted and a rollback will be triggered.

<h4>Rollback</h4>
When a rollback is triggered, the following steps will be executed: <br />
1. Un archive the file system and restore the backup to "files/content"
2. Un archive the database and upload the dump to the database
3. Send a response with the error message to the user
