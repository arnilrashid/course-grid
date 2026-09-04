import os
import re

models = {
    "Order.php": ("OrderStatus", "App\\Enums\\OrderStatus"),
    "Course.php": ("CourseStatus", "App\\Enums\\CourseStatus"),
    "Enrollment.php": ("EnrollmentStatus", "App\\Enums\\EnrollmentStatus"),
    "RefundRequest.php": ("RefundRequestStatus", "App\\Enums\\RefundRequestStatus")
}

directory = "d:/www/coursegrid/app/Models"

for filename, (enum_name, enum_path) in models.items():
    path = os.path.join(directory, filename)
    if not os.path.exists(path):
        continue
    
    with open(path, "r") as f:
        content = f.read()
    
    if "$casts" in content:
        # If $casts already exists, append 'status' to it
        if "status" not in content:
            content = re.sub(r'protected \$casts = \[', f"protected $casts = [\n        'status' => {enum_name}::class,", content)
    else:
        # Add $casts property
        casts_code = f"    protected $casts = [\n        'status' => {enum_name}::class,\n    ];\n\n"
        content = re.sub(r'(protected \$fillable = \[.*?\];)', r'\1\n\n' + casts_code, content, flags=re.DOTALL)
    
    # Add use statement
    if enum_path not in content:
        content = content.replace('use Illuminate\\Database\\Eloquent\\Model;', f'use Illuminate\\Database\\Eloquent\\Model;\nuse {enum_path};')
        
    with open(path, "w") as f:
        f.write(content)

print("Casts added.")
