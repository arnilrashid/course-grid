import os

target_dir = "d:/www/coursegrid/app/Enums"

enums = {
    "OrderStatus.php": """<?php

namespace App\\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case FAILED = 'failed';
}
""",
    "CourseStatus.php": """<?php

namespace App\\Enums;

enum CourseStatus: string
{
    case DRAFT = 'draft';
    case IN_REVIEW = 'in_review';
    case PUBLISHED = 'published';
    case REJECTED = 'rejected';
}
""",
    "EnrollmentStatus.php": """<?php

namespace App\\Enums;

enum EnrollmentStatus: string
{
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
}
""",
    "RefundRequestStatus.php": """<?php

namespace App\\Enums;

enum RefundRequestStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case PROCESSED = 'processed';
}
"""
}

for name, content in enums.items():
    path = os.path.join(target_dir, name)
    with open(path, "w") as f:
        f.write(content)

print("Created all enums.")
