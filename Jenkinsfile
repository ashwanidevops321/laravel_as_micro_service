pipeline {
    agent any

    environment {
        DOCKER_CREDENTIALS_ID = 'dockerhub-credentials'
        SSH_CREDENTIALS_ID    = 'ssh-key-id-in-jenkins'
        NGINX_CONFIG          = 'nginx.conf'
        DOCKER_IMAGE_NAME     = 'ashwanidevops321/lv_app'
        DEPLOY_PATH           = '/home/ubuntu/lv_app'
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
                                scp ${NGINX_CONFIG} ${DEPLOY_USER}@${DEPLOY_HOST}:${DEPLOY_PATH}/nginx.conf

                                # Run container using latest image and mounted nginx.conf
                                ssh ${DEPLOY_USER}@${DEPLOY_HOST} '
                                    docker rm -f lv_nginx || true
                                    docker run -d --name lv_nginx \\
                                        -p 8000:9090 \\
                                        -v ${DEPLOY_PATH}/nginx.conf:/etc/nginx/conf.d/default.conf \\
                                        nginx:alpine

                                    docker rm -f lv_app || true
                                    docker run -d --name lv_app \\
                                        -e ENV=prod \\
                                        -p 9000:9000 \\
                                        ${DOCKER_IMAGE_NAME}:${env.BUILD_ID}
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
