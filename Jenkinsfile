pipeline {
    agent any

    environment {
        DOCKER_CREDENTIALS_ID = 'dockerhub-credentials'
        SSH_CREDENTIALS_ID    = 'ssh-key-id-in-jenkins'
        SCRIPT                = 'docker-compose.yml'
        NGINX_CONFIG          = 'nginx.conf'
        DOCKER_IMAGE_NAME     = 'ashwanidevops321/lv_app'
        DEPLOY_PATH_2         = '/home/ubuntu/lv_app'
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Build Docker Image') {
            steps {
                script {
                    docker.withRegistry('https://index.docker.io/v1/', DOCKER_CREDENTIALS_ID) {
                        def appImage = docker.build("${DOCKER_IMAGE_NAME}:${env.BUILD_ID}", "-f Dockerfile .")
                        appImage.push('latest')
                    }
                }
            }
        }

        stage('Deploy to Server') {
            when {
                expression { fileExists(SCRIPT) }
            }
            steps {
                withCredentials([
                    string(credentialsId: 'deploy-user', variable: 'DEPLOY_USER'),
                    string(credentialsId: 'deploy-host', variable: 'DEPLOY_HOST')
                ]) {
                    script {
                        sshagent([SSH_CREDENTIALS_ID]) {
                            sh """
                                ssh -o StrictHostKeyChecking=no ${DEPLOY_USER}@${DEPLOY_HOST} 'mkdir -p ${DEPLOY_PATH_2}'
                                scp ${SCRIPT} ${DEPLOY_USER}@${DEPLOY_HOST}:${DEPLOY_PATH_2}/docker-compose.yml
                                scp ${NGINX_CONFIG} ${DEPLOY_USER}@${DEPLOY_HOST}:${DEPLOY_PATH_2}/nginx.conf
                                echo "🚀 Deploying containers on remote server..."
                                ssh -o StrictHostKeyChecking=no ${DEPLOY_USER}@${DEPLOY_HOST} '
                                    set -e
                                    cd ${DEPLOY_PATH_2} &&
                                    docker-compose pull &&
                                    docker-compose down &&
                                    docker-compose up -d --remove-orphans
                                '
                            """
                        }
                    }
                }
            }
        }

        stage('Cleanup') {
            steps {
                script {
                    // Optionally clean up old Docker images
                    sh "docker image prune -f"
                }
            }
        }
    }

    post {
        always {
            echo 'Pipeline completed.'
        }
        success {
            echo 'Deployment successful!'
        }
        failure {
            echo 'Deployment failed!'
        }
    }
}