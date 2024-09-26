resource "aws_elasticache_replication_group" "cluster" {
  count                       = local.create_redis_count
  automatic_failover_enabled  = true
  preferred_cache_cluster_azs = local.availability_zones
  replication_group_id        = "${var.environment}-${local.project_short_name}-cluster"
  description                 = "Cluster to Feed"
  num_cache_clusters          = 2
  parameter_group_name        = "default.redis7"
  engine                      = "redis"
  engine_version              = "7.0"
  node_type                   = var.redis_instance_size
  apply_immediately           = true
  port                        = 6379
  subnet_group_name           = aws_elasticache_subnet_group.php-service[0].name

  lifecycle {
    ignore_changes = [num_cache_clusters, engine_version]
  }

  tags     = local.tags
  tags_all = local.tags
}


resource "aws_elasticache_subnet_group" "php-service" {
  count      = local.create_redis_count
  name       = local.project_name
  subnet_ids = data.aws_subnets.default-subnets.ids

  tags     = local.tags
  tags_all = local.tags
}

resource "aws_security_group" "redis" {
  name        = format("rd-%s", local.project_short_name)
  description = "Allow Redis from VPC inbound traffic"
  vpc_id      = data.aws_vpc.default-vpc.id

  ingress {
    description = "Redis from VPC"
    from_port   = 6379
    to_port     = 6379
    protocol    = "tcp"
    cidr_blocks = [data.aws_vpc.default-vpc.cidr_block]
  }

  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }


  tags     = merge({ Name = format("rd-%s", local.project_short_name) }, local.tags)
  tags_all = merge({ Name = format("rd-%s", local.project_short_name) }, local.tags)
}
