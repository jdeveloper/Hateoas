<?php

namespace Hateoas\Configuration\Metadata\Driver;

use Doctrine\Common\Annotations\Reader as AnnotationsReader;
use Hateoas\Configuration\Annotation;
use Hateoas\Configuration\Embed;
use Hateoas\Configuration\Metadata\ClassMetadata;
use Hateoas\Configuration\Relation;
use Hateoas\Configuration\Route;
use Metadata\Driver\DriverInterface;

/**
 * @author Adrien Brault <adrien.brault@gmail.com>
 */
class AnnotationDriver implements DriverInterface
{
    /**
     * @var AnnotationsReader
     */
    private $reader;

    /**
     * @param AnnotationsReader $reader
     */
    public function __construct(AnnotationsReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $annotations = $this->reader->getClassAnnotations($class);

        if (0 === count($annotations)) {
            return null;
        }

        $classMetadata = new ClassMetadata($class->getName());
        $classMetadata->fileResources[] = $class->getFilename();

        foreach ($annotations as $annotation) {
            if ($annotation instanceof Annotation\Relation) {
                $href = $annotation->href;

                if ($href instanceof Annotation\Route) {
                    $href = new Route($href->name, $href->parameters, $href->absolute);
                }

                $embed = $annotation->embed;

                if ($embed instanceof Annotation\Embed) {
                    $embed = new Embed($embed->content, $embed->xmlElementName);
                }

                $classMetadata->addRelation(new Relation(
                    $annotation->name,
                    $href,
                    $embed,
                    $annotation->attributes ?: array()
                ));
            }
        }

        if (0 === count($classMetadata->getRelations())) {
            return null;
        }

        return $classMetadata;
    }
}
