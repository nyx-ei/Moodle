# Moodle 

## Overview

This project aims to set up and customize an instance of Moodle, an open-source online learning platform. Moodle allows you to create rich and interactive learning environments adapted to a variety of educational contexts..

---

## Installation

### Prerequisites

- .NET 5.0 or higher
- Web server: Apache, Nginx or other compatible server.
- Database: MySQL, PostgreSQL, MariaDB, or other compatible DBMS.
- PHP: Version compatible with Moodle (see the official documentation for specific versions).

### Steps

1. Clone the repository:
    ```bash
    git clone https://github.com/nyx-ei/moodle.git  
    ```
    
2. Navigate to the project directory:
    ```bash
    cd Moodle
    ```

3. Restore NuGet packages:
    ```bash
    dotnet restore
    ```

4. Build and run the application:
    ```bash
    dotnet run
    ```

---

## Features

 - Installing Moodle: A step-by-step guide to installing Moodle on a web server.
 - Customization: Instructions to customize the look and functionality of Moodle using themes and plugins.
 - Security: Best practices to secure your Moodle instance.
 - User Management: Tutorials on managing user roles and permissions.
 - Courses and content: Advice on creating and managing courses, activities and educational resources.
 - Integration: Integration with other educational systems and tools.

---

## Usage

Refer to the [User Guide](docs/UserGuide.md) for a comprehensive overview on how to use AutoPM.

---

## Contributing

Contributions from the community are welcome! Please read our [Contributing Guide](docs/CONTRIBUTING.md) to get started.

---

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

---

## Acknowledgements

- The Learning Management System (LMS) architecture
- 

---

For support or queries, feel free to reach out to us at help@nyx-ei.tech.

---

For any queries or support, please reach out to us at help@nyx-ei.tech

---
# Code conventions
## Namespaces
The universal namespace prefix is Moodle. For every class the namespace should respect this structure:
```
Moodle.feature-family.feature.service
```
- **feature-family** is related to how the feature was designed.
- **feature** is the feature associated to your service.
- **service** is the service associated to your class.

_If you have any question related to namespaces convention, feel free to reach out to us at help@nyx-ei.tech._

---
# Architecture
























