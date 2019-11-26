# PortaOne Billing API and Events wrapper for PHP

## Purpose

This package intended to simplify communication to PortaOne billing system while creating user portal applications, integration and provisioning code. Build for composer with PSR-4 autoload, uses PSR-3 object for logging.

*Not properly tested, use at your own risk!*

There two key parts:
- **[Billing API](https://github.com/pavlyuts/portabilling/wiki/Billing-API)** classes wrapping PortaBilling API. Used to create, read, change and remove objects in the billing system.
- **[Billing Event](https://github.com/pavlyuts/portabilling/wiki/Billing-Event)** class to recieve and handle events from the billing system. 

Please, refer [project Wiki](https://github.com/pavlyuts/portabilling/wiki) for details and usage example.

## Installation
In the Composer storage. Just add proper require section:

    "require": {
        "pavlyuts/portabilling": "0.3"
    }
It is a good idea to fix the version you use. Don't use next wersion without review, I can't promose backward compatibility even will try to keep it. Please, review the [changelog](https://github.com/pavlyuts/portabilling/blob/master/CHANGELOG.md) before to change used version.

## Dependencies
- psr/log: ^1.1
- rmccue/requests: ^1.7

## PortaOne documentation
Please, refer to PortaOne documentation and go to the training before use of this package.
- [PortaBilling API docs](https://www.portaone.com/docs/PortaBilling_API.html)
- [External system provisioning framework (ESPF) docs](https://www.portaone.com/docs/PortaSwitch_Interfaces.pdf#page=45)
- [ESPF event list and passed values](https://www.portaone.com/docs/PortaSwitch_Interfaces.pdf#page=55)
- [Provisioning Application Reference Guide](https://www.portaone.com/docs/Provisioning_Application_Guide.pdf)
- [ESPF configuration handbook](https://www.portaone.com/handbook/index.htm#t=External_System_Provisioning%2FESPF_Configuration%2FESPF_Configuration.htm)
- [Administrator guide](https://www.portaone.com/docs/PortaBilling_Admin_Guide.pdf)
