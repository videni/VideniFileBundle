VideniFileBundle
===============
Add host to entity properties automatically with JMS Serializer, this bundle assumes that you upload file with a dedicated controller, then save the responded file path to property of an entity.

# Do you need this bundle?

You upload file using a dedicated controller, then save responded  file path to a property of an entity,  it will be troublesome after a white you want to switch to another CDN provider if the file path has hardcoded host.

# Feature

1. Add host to file path automatically when serialize entity with JMS serializer

2. A dedicated controller for file upload.

3. Integrated with flysytem and `vich/uploader-bundle`


# Configuration

1. Add default mapping to vich_uploader
this bundle assumes the `default` mapping is existed at `vich_uploader` section
```
vich_uploader:
    db_driver: orm
    storage: flysystem
    metadata:
        cache: file
    mappings:
        default:
            uri_prefix: /media
            upload_destination: default_filesystem
            namer: Vich\UploaderBundle\Naming\UniqidNamer
```

2. Specify asset host and user class 
```
videni_file:
    asset_endpoint: "%env(ASSET_HOST)%"
    user_entity_class: 'App\Entity\User'
```

# How to use?

Add file annotation to an entity
```
/**
 * @FileAnnoation\File()
 */
class UseCase
{
    /**
     * @FileAnnoation\Link()
     */
    private $logo;
}
```
