# Default
variable "region" {
  type        = string
  description = "us-east-1"
}

variable "environment" {
  type        = string
  description = "Deployment environment identifier (one of [qa, prod])"
}

# APP Auto Scaling
variable "appautoscaling" {
  type = object({
    max_capacity              = number
    min_capacity              = number
    policy_target_value       = number
    policy_scale_in_cooldown  = number
    policy_scale_out_cooldown = number
  })
  description = "APP Auto Scaling values"
}

variable "redis_instance_size" {
  type        = string
  description = "Instance type of redis"
}

variable "TFC_CONFIGURATION_VERSION_GIT_COMMIT_SHA" {}
