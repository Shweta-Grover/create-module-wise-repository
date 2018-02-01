<?php
namespace App\CreateModuleReposiitory\repo\Commands;

use Illuminate\Console\Command;

class MakeRepositoryCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make-repository {repoName} {module} {--model=} {--interface=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository for specified module';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $modelName = $this->option('model');

            $repoName = $this->argument('repoName');

            $moduleName = $this->argument('module');

            $interfaceName = $this->option('interface');

            $moduleName = $this->argument('module');

            $filePath = "Modules/" . $moduleName . "/Repositories/" . $repoName . ".php";

            // if repository already exists
            if (file_exists($filePath)) {
                $this->info("Repository already exists");
            } else {
                $repositoryParameters = new \stdClass();

                $repoFile = fopen($filePath, "w");

                $this->createRepoRequestObject($repositoryParameters, $repoName, $modelName, $repoFile, $interfaceName, $moduleName);

                $this->definePriorToClass($repositoryParameters);

                $this->defineClassStructure($repositoryParameters);

                fclose($repoFile);

                $this->info("Repository created successfully");
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * This function define structure prior to class definition.
     *
     * @param \stdClass $repositoryParameters request parameters to create repository file
     */
    private function definePriorToClass($repositoryParameters)
    {
        $phpOpeningTag = "<?php\r\n";

        fwrite($repositoryParameters->repoFile, $phpOpeningTag);

        $nameSpace = "\r\nnamespace Modules\\" . $repositoryParameters->moduleName . "\Repositories;\r\n\r\n";

        fwrite($repositoryParameters->repoFile, $nameSpace);

        // if model option is given
        if ($repositoryParameters->modelName != null) {
            $modelFilePath = "Modules/" . $repositoryParameters->moduleName . "/Entities/" . $repositoryParameters->modelName . ".php";
            if (! file_exists($modelFilePath)) {
                // $modelFile = fopen($modelFilePath,"w");
                $this->createModelFile($repositoryParameters);
            }
            $this->useInterfaceModelCode($repositoryParameters);
        }
    }

    /**
     * This function define class structure while defining class.
     *
     * @param \stdClass $repositoryParameters request parameters to create repository file
     */
    private function defineClassStructure($repositoryParameters)
    {
        $this->addClassComments($repositoryParameters->repoFile);

        $classStructure = "class " . $repositoryParameters->repoName;
        fwrite($repositoryParameters->repoFile, $classStructure);

        // if interface option is given
        if ($repositoryParameters->interfaceName != null) {
            $interfaceImplementCode = " implements " . $repositoryParameters->interfaceName;
            fwrite($repositoryParameters->repoFile, $interfaceImplementCode);
        }

        $classOpeningBrace = "\r\n{\r\n";

        fwrite($repositoryParameters->repoFile, $classOpeningBrace);

        // if model option is given
        if ($repositoryParameters->modelName != null) {
            $this->constructorCode($repositoryParameters);
        }

        $classClosing = "\r\n}";

        fwrite($repositoryParameters->repoFile, $classClosing);
    }

    /**
     * Convert sting to camel Case.
     *
     * @param string $originalString string to be converted to camelCase.
     *
     * @return string $camelCaseString string after conversion
     */
    private function convertToCamelCase($originalString)
    {
        $originalString = ucwords($originalString);
        $originalString = str_replace(" ", "", $originalString);
        $camelCaseString = lcfirst($originalString);
        return $camelCaseString;
    }

    /**
     * This function define use code required to inject model in repository.
     *
     * @param \stdClass $repositoryParameters request parameters to create repository file
     */
    private function useInterfaceModelCode($repositoryParameters)
    {
        $modelLibraryPath = "use Illuminate\Database\Eloquent\Model;\r\n";

        fwrite($repositoryParameters->repoFile, $modelLibraryPath);

        // if interface option is given
        if ($repositoryParameters->interfaceName != null) {
            $interfaceFilePath = "Modules\\" . $repositoryParameters->moduleName . "\Repositories\\" . $repositoryParameters->interfaceName . ".php";
            if (! file_exists($interfaceFilePath)) {
                $interfaceFile = fopen($interfaceFilePath, "w");
                $this->createInterfaceFile($repositoryParameters, $interfaceFile);
            }

            $useInterfaceCode = "use Modules\\" . $repositoryParameters->moduleName . "\Repositories\\" . $repositoryParameters->interfaceName . ";\r\n\r\n";
            fwrite($repositoryParameters->repoFile, $useInterfaceCode);
        } else {
            fwrite($repositoryParameters->repoFile, "\r\n");
        }
    }

    /**
     * This function define code for constructor injecting dependency of Model.
     *
     * @param \stdClass $repositoryParameters request parameters to create repository file
     */
    private function constructorCode($repositoryParameters)
    {
        $modelVariable = "\tprivate $" . $this->convertToCamelCase($repositoryParameters->modelName) . ";\r\n\r\n";

        fwrite($repositoryParameters->repoFile, $modelVariable);

        $camelCaseModelName = $this->convertToCamelCase($repositoryParameters->modelName);

        $modelDependency = "\tpublic function __construct(Model $" . $camelCaseModelName . ")\r\n";

        fwrite($repositoryParameters->repoFile, $modelDependency);

        $constructorStructure = "\t{\r\n\t\t" . '$this->' . $camelCaseModelName . "= $" . $camelCaseModelName . ";\r\n\t}";

        fwrite($repositoryParameters->repoFile, $constructorStructure);
    }

    /**
     * This function add properties to ibject needed to each function to create class.
     *
     * @param \stdClass $repositoryParameters request parameters to create repository file
     * @param string $repoName repository file name
     * @param string $modelName model name
     * @param resource $repoFile repository class file resource to be modified
     * @param string $interfaceName interace name
     * @param string $moduleName module name
     */
    private function createRepoRequestObject(&$repositoryParameters, $repoName, $modelName, $repoFile, $interfaceName, $moduleName)
    {
        $repositoryParameters->repoName = $repoName;
        $repositoryParameters->modelName = $modelName;
        $repositoryParameters->repoFile = $repoFile;
        $repositoryParameters->interfaceName = $interfaceName;
        $repositoryParameters->moduleName = $moduleName;
    }

    /**
     * This function creates model file in Modules/Entities folder if does not exist.
     *
     * @param \stdClass $repositoryParameters request parameters to create repository file
     */
    private function createModelFile($repositoryParameters)
    {
        $this->call('module:make-model', [
            'model' => $repositoryParameters->modelName,
            'module' => $repositoryParameters->moduleName
        ]);
    }

    /**
     * This function creates model file in App/Entities folder if does not exist.
     *
     * @param \stdClass $repositoryParameters request parameters to create repository file
     * @param resource $interfaceFile interface file to be modified
     */
    private function createInterfaceFile($repositoryParameters, $interfaceFile)
    {
        $phpOpeningTag = "<?php\r\n";

        fwrite($interfaceFile, $phpOpeningTag);

        $namespacePath = "\r\nnamespace Modules\\" . $repositoryParameters->moduleName . "\Repositories;\r\n\r\n";

        fwrite($interfaceFile, $namespacePath);

        $interfaceName = "interface " . $repositoryParameters->interfaceName;

        fwrite($interfaceFile, $interfaceName);

        $interfaceOpeningBrace = "\r\n{\r\n\t//";

        fwrite($interfaceFile, $interfaceOpeningBrace);

        $interaceClosingBrace = "\r\n}";

        fwrite($interfaceFile, $interaceClosingBrace);
    }

    /**
     * This function add comments to class.
     *
     * @param resource $repoFile repository class file resource to be modified
     */
    private function addClassComments(&$repoFile)
    {
        $classCommentsStart = "/**\r\n*\r\n";

        fwrite($repoFile, $classCommentsStart);

        $authorComment = "*@author php artisan module:make-repository {repoName} {moduleName} --model={modelName} --interface={interfaceName}\r\n";

        fwrite($repoFile, $authorComment);

        $version = "*@version v1\r\n";

        fwrite($repoFile, $version);

        $classCommentEnd = "*\r\n*/\r\n";

        fwrite($repoFile, $classCommentEnd);
    }
}
