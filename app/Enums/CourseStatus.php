<?php

namespace App\Enums;

enum CourseStatus: string
{
    case DRAFT = 'draft';
    case IN_REVIEW = 'in_review';
    case PUBLISHED = 'published';
    case REJECTED = 'rejected';
}
