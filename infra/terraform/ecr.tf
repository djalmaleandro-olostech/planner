resource "aws_ecr_repository" "ecr-php-service" {
  name                 = local.ecr_repo_name
  image_tag_mutability = "MUTABLE"
  force_delete         = true
  tags                 = merge({ Name = local.ecr_repo_name }, local.tags)
  tags_all             = merge({ Name = local.ecr_repo_name }, local.tags)
}

resource "aws_ecr_repository_policy" "ecr-policy" {
  repository = aws_ecr_repository.ecr-php-service.name
  policy     = file("./policies/ecr_policy.json")
}
