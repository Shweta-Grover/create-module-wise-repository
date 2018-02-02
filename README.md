# create-module-wise-repository
First you need to install nWidrat/laravel-modules to create modules in your project.

To create repository fro specific module

Run composer require  createmodulewiserepo/repo dev-master

add createmodulewiserepo\repo\Providers\MakeRepositoryServiceProvider::class
in your config/app.php file

Run composer dump-autoload

Run  php artisan module:make-repository {repositoryName} {moduleName} --model={modelName} --interface={interfaceName}.

Repository name: Name of class of repository

Module Name : Particular module name in which repository will be created.

Repository file will be created in App/Modules/<Module Name>/Repositories
  
Model Name : Model for repository will be injected in specified Repository Constructor

Interface Name : Interface to whom repository will implement.

Model and Interface will be created if they don't exist

Repository and module name are mandatory. Model and interface name are optional.
