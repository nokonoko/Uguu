provider "aws" {}

variable "uguu_bucket_name" {
  type        = string
  description = "Bucket Name to be used for Uguu Storage Backend"
}

variable "retention_days" {
  type        = number
  description = "Number of hours for lifecycle policy to retain files before deleting them"
  default     = 2
}

resource "aws_s3_bucket" "uguu_bucket" {
  bucket = var.uguu_bucket_name
}

resource "aws_s3_bucket_lifecycle_configuration" "uguu_lc_policy" {
  bucket = aws_s3_bucket.uguu_bucket.id
  rule {
    id     = "delete-after-x-days"
    status = "Enabled"
    expiration {
      days = var.retention_days
    }
  }
}

resource "aws_s3_bucket_public_access_block" "uguu_public_block_policy" {
  bucket = aws_s3_bucket.uguu_bucket.id
}

resource "aws_s3_bucket_policy" "uguu_bucket_policy" {
  bucket = aws_s3_bucket.uguu_bucket.id
  policy = data.aws_iam_policy_document.allow_public_access.json
}

data "aws_iam_policy_document" "allow_public_access" {
  statement {
    principals {
      type        = "*"
      identifiers = ["*"]
    }

    actions = [
      "s3:GetObject"
    ]

    resources = [
      "${aws_s3_bucket.uguu_bucket.arn}/*"
    ]
  }
}