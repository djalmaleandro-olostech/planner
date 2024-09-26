resource "aws_iam_role" "ecs-task-role" {
  name        = "role-ecs-task-${local.project_name}"
  description = "ECS Task Role"
  assume_role_policy = jsonencode({
    Version = "2008-10-17"
    Statement = [
      {
        Action : "sts:AssumeRole",
        Principal : {
          Service : ["ecs-tasks.amazonaws.com"]
        },
        Effect : "Allow"
      }
    ]
  })

  tags     = local.tags
  tags_all = local.tags
}

resource "aws_iam_role_policy" "ecs-task-policy" {
  name   = "policy-ecs-${local.project_name}"
  role   = aws_iam_role.ecs-task-role.id
  policy = file("./policies/policy-ecs-task-execution.json")
}

resource "aws_iam_role" "ecs-auto-scaling-role" {
  name        = "role-ecs-auto-scaling-${local.project_name}"
  description = "The ECS auto-scaling role"

  assume_role_policy = jsonencode({
    Version = "2008-10-17"
    Statement = [
      {
        Action : "sts:AssumeRole",
        Principal : {
          Service : ["ecs.application-autoscaling.amazonaws.com"]
        },
        Effect : "Allow"
      }
    ]
  })

  tags     = local.tags
  tags_all = local.tags
}

resource "aws_iam_role_policy" "ecs-auto-scaling-policy" {
  name   = "policy-ecs-auto-scaling-${local.project_name}"
  role   = aws_iam_role.ecs-auto-scaling-role.id
  policy = file("./policies/policy-ecs-auto-scaling-execution.json")
}
