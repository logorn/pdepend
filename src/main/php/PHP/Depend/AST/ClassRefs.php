<?php
class PHP_Depend_AST_ClassRefs
{
    /**
     * @var PHP_Depend_Context
     */
    private $context;

    /**
     * @var string
     */
    private $namespaceId;

    /**
     * @var string
     */
    private $parentClassId;

    /**
     * @var string[]
     */
    private $implementedInterfaceIds;

    /**
     * asdasd
     *
     * @param PHP_Depend_Context $context
     * @param string             $namespaceId
     * @param string             $parentClassId
     * @param string[]           $implementedInterfaceIds
     */
    public function __construct(PHP_Depend_Context $context, $namespaceId, $parentClassId, array $implementedInterfaceIds)
    {
        $this->context                 = $context;
        $this->namespaceId             = $namespaceId;
        $this->parentClassId           = $parentClassId;
        $this->implementedInterfaceIds = $implementedInterfaceIds;
    }

    public function getNamespace()
    {
        if ($namespace = $this->context->getNamespace($this->namespaceId)) {
            return $namespace;
        }
        // TODO Return dummy namespace
    }

    public function getParentClass()
    {
        if (null === $this->parentClassId) {
            return null;
        }
        return $this->context->getClass($this->parentClassId);
    }

    /**
     * @return PHP_Depend_AST_Interface[]
     */
    public function getImplementedInterfaces()
    {
        $implemented = array();
        foreach ($this->implementedInterfaceIds as $interfaceId) {

            $implemented[] = $this->context->getInterface($interfaceId);
        }
        return $implemented;
    }

    public function initialize(PHP_Depend_AST_Class $class)
    {
        $this->context->registerNode($class);
    }
}