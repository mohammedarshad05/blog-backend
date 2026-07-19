# Step-by-Step Symfony Product Feature Guide

This document outlines the step-by-step process used to build the **Product** management feature (CRUD) in your Symfony application.

---

## Step 1: Create the Database Entity
An **Entity** represents a database table in Symfony. We created the `Product` entity to define the columns/properties of our products.

### 💻 Terminal Command
```bash
php bin/console make:entity Product
```
*During the wizard, we added the fields `name` (string), `description` (text), and `size` (integer).*

### 📝 Simplified Code (`src/Entity/Product.php`)
```php
namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 128)]
    #[Assert\NotBlank(message: 'Product name is required')]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\NotBlank(message: 'Description is required')]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotNull(message: 'Size is required')]
    #[Assert\Positive(message: 'Size must be a positive number')]
    private ?int $size = null;

    // Getters and setters (getId, getName, setName, etc.) are below this...
}
```

---

## Step 2: Generate & Run Database Migrations
Once the Entity is created/updated, you must tell the database to create the corresponding table.

### 💻 Terminal Commands
1. **Generate the migration file** (compares your PHP entities with the database schema and writes the SQL query):
   ```bash
   php bin/console make:migration
   ```
2. **Execute the migration** (applies the SQL to create the `product` table in the database):
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

---

## Step 3: Create the Form Type
Symfony uses **Form Classes** to build and validate HTML forms that map directly to our Entities.

### 💻 Terminal Command
```bash
php bin/console make:form ProductType Product
```

### 📝 Simplified Code (`src/Form/ProductType.php`)
```php
namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('size')
            ->add('save', SubmitType::class, [
                'label' => 'Create Product',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
```

---

## Step 4: Create the Controller (CRUD Logic)
The **Controller** handles HTTP requests, interacts with the database, and renders templates.

### 💻 Terminal Command
```bash
php bin/console make:controller ProductController
```

### 📝 Simplified Code (`src/Controller/ProductController.php`)
```php
namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    // 1. LIST ALL PRODUCTS
    #[Route('/products', name: 'app_product')]
    public function index(ProductRepository $repository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $repository->findAll(),
        ]);
    }

    // 2. SHOW A SINGLE PRODUCT (using ParamConverter to fetch automatically by id)
    #[Route('/product/{id<\d+>}', name: 'app_product_show')]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    // 3. CREATE A NEW PRODUCT
    #[Route('/product/new', name: 'product_new')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($product); // Stage to database
            $manager->flush();           // Execute SQL query
            
            $this->addFlash('success', 'Product created successfully!');
            return $this->redirectToRoute('app_product');
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // 4. EDIT AN EXISTING PRODUCT
    #[Route('/product/{id<\d+>}/edit', name: 'product_edit')]
    public function edit(Product $product, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush(); // Updates the existing product automatically
            
            $this->addFlash('success', 'Product updated successfully!');
            return $this->redirectToRoute('app_product');
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form,
        ]);
    }

    // 5. DELETE A PRODUCT
    #[Route('/product/{id<\d+>}/delete', name: 'product_delete')]
    public function delete(Request $request, Product $product, EntityManagerInterface $manager): Response
    {
        if ($request->isMethod('POST')) {
            $manager->remove($product); // Mark for deletion
            $manager->flush();          // Execute SQL DELETE query

            $this->addFlash('notice', 'Product deleted successfully');
            return $this->redirectToRoute('app_product');
        }

        return $this->render('product/delete.html.twig', [
            'id' => $product->getId(),
        ]);
    }
}
```

---

## Step 5: Create Twig Templates
Finally, templates inside `templates/product/` render the HTML sent to the browser:

1. **`index.html.twig`**: Displays the list of all products using `{% for product in products %}`.
2. **`show.html.twig`**: Renders details of a single product.
3. **`_form.html.twig`**: A reusable partial template rendering the form widget:
   ```twig
   {{ form_start(form) }}
       {{ form_widget(form.name) }}
       {{ form_widget(form.description) }}
       {{ form_widget(form.size) }}
   {{ form_end(form) }}
   ```
4. **`new.html.twig`**: Imports `_form.html.twig` to display the creation form.
5. **`edit.html.twig`**: Imports `_form.html.twig` populated with the selected product's data to edit it.
6. **`delete.html.twig`**: Confirms deletion via a POST form.
