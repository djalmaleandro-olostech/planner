resource "aws_security_group" "allow_web" {
  name        = format("web-%s", local.project_short_name)
  description = "Allow from VPC inbound traffic"
  vpc_id      = data.aws_vpc.default-vpc.id

  ingress {
    description = "All from from VPC"
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = [data.aws_vpc.default-vpc.cidr_block]
  }

  ingress {
    description = "All from from VPN Range (Old Account)"
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = [data.aws_ssm_parameter.vpn_endpoint.value]
  }

  ingress {
    description = "All from from DEV Account Range (Old Account)"
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["10.1.0.0/16"]
  }



  egress {
    from_port        = 0
    to_port          = 0
    protocol         = "-1"
    cidr_blocks      = ["0.0.0.0/0"]
    ipv6_cidr_blocks = ["::/0"]
  }

  tags     = merge({ Name = format("web-%s", local.project_short_name) }, local.tags)
  tags_all = merge({ Name = format("web-%s", local.project_short_name) }, local.tags)
}
