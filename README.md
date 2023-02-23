<div id="top"></div>

[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
[![MIT License][license-shield]][license-url]
[![LinkedIn][linkedin-shield]][linkedin-url]

<!-- ------------------------------------------------------------------------- -->
<br />
<div align="center">
  <a href="https://github.com/BROCKHAUS-AG/contao-release-stages-bundle">
    <img src="images/logo.svg" alt="Logo" width="120">
  </a>

<h3 align="center">Contao Release Stages Bundle</h3>

  <p align="center">
    With the Contao release stages bundle you would be able to easily draft and upload new releases to your prod stage.
    <br />
    <a href="https://github.com/BROCKHAUS-AG/contao-release-stages-bundle"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="https://github.com/BROCKHAUS-AG/contao-release-stages-bundle/issues">Report Bug</a>
    ·
    <a href="https://github.com/BROCKHAUS-AG/contao-release-stages-bundle/issues">Request Feature</a>
  </p>
</div>
<!-- ------------------------------------------------------------------------- -->

<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
      <ul>
        <li><a href="#built-with">Built With</a></li>
      </ul>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#usage">Usage</a></li>
  </ol>
</details>
<!-- ------------------------------------------------------------------------- -->


## About The Project

To now how a release/deployment process works, you could have a look at the following
[diagram](images/deploymentProcess.png). Also, you can have a look at the description
[here](README_deploymentProcess.md).

<p align="right">(<a href="#top">back to top</a>)</p>
<!-- ------------------------------------------------------------------------- -->

### Built With

* [PHP 7.4](https://www.php.net/releases/7_4_0.php)
* [Symfony 4](https://symfony.com/4)

<p align="right">(<a href="#top">back to top</a>)</p>
<!-- ------------------------------------------------------------------------- -->

## Getting Started

### Prerequisites
Before you can install the contao release stages bundle, you have to meet certain prerequisites.
- An installation of minimum PHP 7.4
- Contao 4.9 and higher

### Installation

1. You have to use ```composer``` to install the bundle. The command would be
```composer require brockhaus-ag/contao-release-stages-bundle``` or you can also install it with the Contao manager.
2. Configure the configuration file
   1. You can find an example configuration here [settings/config_example.json](settings/config_example.json).

<p align="right">(<a href="#top">back to top</a>)</p>
<!-- ------------------------------------------------------------------------- -->

## Usage
- Click in the Contao menu section on the left side on "Release Generator". There you would be able to create a new release.
- If you clicked on "new"
  - you could change the kind of release (this is only to show a nicer history)
  - set a title and a description

<p align="right">(<a href="#top">back to top</a>)</p>
<!-- ------------------------------------------------------------------------- -->

## Things that work and don't work

### Working
- Create automatically tables in database
- Copy database table content
- Copy directory

### Not working
- Copy database relationships from test stage to prod stage

<p align="right">(<a href="#top">back to top</a>)</p>
<!-- ------------------------------------------------------------------------- -->

[contributors-shield]: https://img.shields.io/github/contributors/BROCKHAUS-AG/contao-release-stages-bundle?style=for-the-badge
[contributors-url]: https://github.com/BROCKHAUS-AG/contao-release-stages-bundle/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/BROCKHAUS-AG/contao-release-stages-bundle?style=for-the-badge
[forks-url]: https://github.com/BROCKHAUS-AG/contao-release-stages-bundle/network/members
[stars-shield]: https://img.shields.io/github/stars/BROCKHAUS-AG/contao-release-stages-bundle?style=for-the-badge
[stars-url]: https://github.com/BROCKHAUS-AG/contao-release-stages-bundle/stargazers
[issues-shield]: https://img.shields.io/github/issues/BROCKHAUS-AG/contao-release-stages-bundle?style=for-the-badge
[issues-url]: https://github.com/BROCKHAUS-AG/contao-release-stages-bundle/issues
[license-shield]: https://img.shields.io/github/license/BROCKHAUS-AG/contao-release-stages-bundle?style=for-the-badge
[license-url]: https://github.com/BROCKHAUS-AG/contao-release-stages-bundle/blob/master/LICENSE.txt
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-black.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://www.linkedin.com/company/brockhaus-ag
