pipeline {
    agent any

    environment {
        DOCKER_CREDENTIALS_ID = 'dockerhub-credentials'
        SSH_CREDENTIALS_ID    = 'ssh-key-id-in-jenkins'
        NGINX_CONFIG          = 'nginx.conf'
        DOCKER_IMAGE_NAME     = 'ashwanidevops321/lv_app'
        DEPLOY_PATH           = '/home/ubuntu/lv_app'
        SCRIPT                = 'docker-compose.yml'
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Build & Push Image') {
            steps {
                script {
                    docker.withRegistry('https://index.docker.io/v1/', DOCKER_CREDENTIALS_ID) {
                        def image = docker.build("${DOCKER_IMAGE_NAME}:${env.BUILD_ID}")
                        image.push("${env.BUILD_ID}")
                        image.push('latest')
                    }
                }
            }
        }

        stage('Deploy NGINX Config & Run') {
            steps {
                withCredentials([
                    string(credentialsId: 'deploy-user', variable: 'DEPLOY_USER'),
                    string(credentialsId: 'deploy-host', variable: 'DEPLOY_HOST')
                ]) {
                    script {
                        sshagent([SSH_CREDENTIALS_ID]) {
                            sh """
                                ssh -o StrictHostKeyChecking=no ${DEPLOY_USER}@${DEPLOY_HOST} '
                                    mkdir -p ${DEPLOY_PATH}
                                '

                                # Copy only nginx.conf to server
                                scp ${SCRIPT} ${DEPLOY_USER}@${DEPLOY_HOST}:${DEPLOY_PATH}/docker-compose.yml
                                scp ${NGINX_CONFIG} ${DEPLOY_USER}@${DEPLOY_HOST}:${DEPLOY_PATH}/nginx.conf

                                # Run container using latest image and mounted nginx.conf
                                echo "🚀 Deploying containers on remote server..."
                                ssh -o StrictHostKeyChecking=no ${DEPLOY_USER}@${DEPLOY_HOST} '
                                    set -e
                                    cd ${DEPLOY_PATH} &&
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
                sh "docker image prune -f"
            }
        }
    }

    post {
        success {
            echo '✅ Deployment successful!'
        }
        failure {
            echo '❌ Deployment failed!'
        }
        always {
            echo 'Pipeline completed.'
        }
    }
}
